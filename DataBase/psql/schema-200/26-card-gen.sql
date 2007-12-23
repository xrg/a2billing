-- Functions for automatic generation of cards

CREATE OR REPLACE FUNCTION gen_seraliases(s_crdgrp INTEGER, s_num INTEGER, s_start BIGINT, s_len INTEGER)
	RETURNS SETOF TEXT AS $$
	SELECT foo.ser
		FROM ( SELECT lpad(generate_series($3,$3+$2-1),$4,'0') AS ser) AS foo
		WHERE foo.ser NOT IN ( SELECT useralias FROM cc_card, 
					cc_card_group AS g1,
					cc_card_group AS g2
					WHERE cc_card.grp = g1.id 
					AND g1.numplan = g2.numplan 
					AND g2.id = $1);
$$ LANGUAGE SQL STRICT VOLATILE;

CREATE OR REPLACE FUNCTION gen_rndaliases(s_crdgrp INTEGER, s_num INTEGER, s_start BIGINT, s_len INTEGER)
	RETURNS SETOF TEXT AS $$
	SELECT DISTINCT foo.rnd
		FROM ( SELECT generate_series(1,$2) AS ser, mknumpasswd($4) AS rnd) AS foo
		WHERE foo.rnd NOT IN ( SELECT useralias FROM cc_card, 
					cc_card_group AS g1,
					cc_card_group AS g2
					WHERE cc_card.grp = g1.id 
					AND g1.numplan = g2.numplan 
					AND g2.id = $1);
$$ LANGUAGE SQL STRICT VOLATILE;

CREATE OR REPLACE FUNCTION gen_uname(s_pattern TEXT, s_agname TEXT,s_alias TEXT) RETURNS TEXT
AS $$
DECLARE
	plen INTEGER;
BEGIN
	IF substr(s_pattern,1,2) = '%#' THEN
		plen := (substr(s_pattern,3))::INTEGER;
		IF plen < 3 OR plen >20 THEN
			RAISE EXCEPTION 'Invalid pattern!';
		END IF;
		RETURN mknumpasswd(plen);
	ELSE
		RETURN replace(replace(s_pattern,'%1',s_alias),'%2',s_agname);
	END IF;

END; $$ LANGUAGE plpgsql STRICT IMMUTABLE;

/** Generate cards and optionally VoIP peers
    \param s_cardgrp The card group to use. It also defines the numplan.
    \param s_serial  If true, generate consecutive numbers as user-aliases,
                     else, generate random.
    \param s_num    Number of cards to generate
    \param s_start  Starting number of user aliases. if empty, use MAX(useralias)
    \param s_ucfg   If >0, generate asterisk users using that cc_ast_users_config group.
*/
CREATE OR REPLACE FUNCTION gen_cards(s_crdgrp INTEGER, s_serial boolean, s_num INTEGER, s_start TEXT,
		s_ucfg INTEGER) RETURNS INTEGER AS $$
DECLARE
	dloop INTEGER;
	dremain INTEGER;
	planrow RECORD;
	din	INTEGER;
	dstart  BIGINT;
BEGIN
	dloop:=0;
	dremain :=s_num;
	
	SELECT cc_card_group.id AS grp,agentid,numplan,uname_pattern,initiallimit,
			def_currency,aliaslen, COALESCE(cc_agent.login,'') AS agentname
		INTO planrow
		FROM cc_card_group LEFT JOIN cc_agent ON cc_card_group.agentid = cc_agent.id ,
			cc_numplan 
		WHERE cc_card_group.id = s_crdgrp
		  AND cc_card_group.numplan = cc_numplan.id;
	IF NOT FOUND THEN
		RAISE EXCEPTION 'Card group not found!';
	END IF;

	--RAISE NOTICE 'Row: %',planrow;
	dstart:= s_start::BIGINT;

	LOOP
		--RAISE NOTICE 'Loop %',dloop;
		IF dloop > 100 THEN
			RAISE EXCEPTION 'Cannot find usable name/alias';
		END IF;
		
		dloop := dloop + 1;

		IF s_serial THEN
		    INSERT INTO cc_card(grp,username,useralias,userpass,credit,status,
		    		currency,creditlimit)
			SELECT planrow.grp,gen_uname(planrow.uname_pattern,planrow.agentname,foo.alias),
				foo.alias, mkpasswd(8),0.0,2,planrow.def_currency,planrow.initiallimit
				FROM ( SELECT gen_seraliases(planrow.grp, dremain, 
					dstart,planrow.aliaslen) AS alias) AS foo;
			
		    GET DIAGNOSTICS din = ROW_COUNT;
		    -- RAISE NOTICE 'Din : %, dremain: %',din,dremain;
		    dstart := dstart + dremain;
		    dremain :=dremain - din;
		
		ELSE
		    INSERT INTO cc_card(grp,username,useralias,userpass,credit,status,
		    		currency,creditlimit)
			SELECT planrow.grp,gen_uname(planrow.uname_pattern,planrow.agentname,foo.alias),
				foo.alias, mkpasswd(8),0.0,2,planrow.def_currency,planrow.initiallimit
				FROM ( SELECT gen_rndaliases(planrow.grp, dremain, 
					dstart,planrow.aliaslen) AS alias) AS foo;
			
		    GET DIAGNOSTICS din = ROW_COUNT;
		    dremain :=dremain - din;
		END IF;
		
		EXIT WHEN dremain <= 0 ;
	END LOOP;
	
	-- now() is atomic within this function, so it would be safe to select those cards
	-- by their creation time.
	IF s_ucfg >0  THEN
		INSERT INTO cc_ast_users(card_id,config) 
			SELECT id, s_ucfg FROM cc_card 
			WHERE grp = planrow.grp 
			  AND status = 2 
			  AND creationdate = now();
	END IF;

	RETURN s_num - dremain;
END; $$ LANGUAGE PLPGSQL VOLATILE;

--eof
