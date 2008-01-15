-- All the functionality of realtime users/peers is done through
-- views, that combine several tables.

-- The use of views actually helps prevent * from updating the wrong
-- fields.

CREATE OR REPLACE VIEW cc_ast_users_v AS
	SELECT cc_ast_users.*,
	(CASE WHEN cc_card.id IS NOT NULL THEN 'Card:' || cc_card.username
		WHEN cc_booth.id IS NOT NULL THEN 'Booth:' || cc_booth.name
		ELSE '' END) AS name
	FROM cc_ast_users
		LEFT JOIN cc_card ON (cc_ast_users.card_id = cc_card.id)
		LEFT JOIN cc_booth ON (cc_ast_users.booth_id = cc_booth.id)
	;
	

CREATE OR REPLACE VIEW realtime_sip_peers AS
SELECT COALESCE(cc_card.username, cc_booth.peername) AS name,
	(CASE WHEN cc_card.id IS NOT NULL THEN 'card:' || cc_card.id::TEXT
		WHEN cc_booth.id IS NOT NULL THEN 'booth:' || cc_booth.id::TEXT
		ELSE '' END) AS accountcode,
	COALESCE(cc_card.userpass, cc_booth.peerpass) AS secret,
	cc_ast_users.id AS realtime_id,
	"type", "context", videosupport, fromdomain, amaflags, dtmfmode,
	defaultip, fromuser, host,
	progressinband, incominglimit, outgoinglimit,
	nat, canreinvite, insecure,
	rtpkeepalive, rtpholdtimeout, rtptimeout,
	qualify,
	permit, deny, mask,
	disallow, allow,
	cancallforward, musiconhold, setvar,
	ipaddr , COALESCE(port, defport) AS port, regseconds,
	cc_ast_instance.username, COALESCE(fullcontact,'')::varchar(80) AS fullcontact, regserver, useragent
	
	FROM cc_ast_users_config, cc_ast_users
		LEFT JOIN cc_ast_instance ON (cc_ast_instance.userid = cc_ast_users.id 
			AND cc_ast_instance.sipiax = 1
			AND cc_ast_instance.dyn = true
			AND cc_ast_instance.srvid = ( SELECT id from cc_a2b_server 
				WHERE db_username = current_user))
		LEFT JOIN cc_card ON (cc_ast_users.card_id = cc_card.id)
		LEFT JOIN cc_booth ON (cc_ast_users.booth_id = cc_booth.id)
	
	WHERE cc_ast_users_config.id = cc_ast_users.config
		AND cc_ast_users.has_sip = true
		
	;
	
-- TODO: Disabled peers!


-- LEFT OUTER JOIN cc_ast_instance ON cau.id = cc_ast_instance.userid


--- Now, define update rules for the views. That way, asterisk will be
-- able to update *only some* of the information, such as where the user
-- is actually registered.
CREATE OR REPLACE RULE realtime_sip_update_rn AS ON UPDATE TO realtime_sip_peers
	DO INSTEAD NOTHING;

-- First case: registration of a sip peer: insert the instance
CREATE OR REPLACE RULE realtime_sip_update_ri AS ON UPDATE TO realtime_sip_peers
	WHERE OLD.ipaddr IS NULL AND NEW.ipaddr IS NOT NULL AND NEW.ipaddr <> ''
	DO INSTEAD
	INSERT INTO cc_ast_instance(userid, srvid, dyn,sipiax,ipaddr,port, regseconds,
			username, fullcontact, regserver, useragent)
		VALUES(NEW.realtime_id, ( SELECT id from cc_a2b_server WHERE db_username = current_user),
			true,1,NEW.ipaddr,NEW.port,NEW.regseconds,NEW.username,NEW.fullcontact,NEW.regserver, NEW.useragent);

CREATE OR REPLACE RULE realtime_sip_update_r3 AS ON UPDATE TO realtime_sip_peers
	WHERE OLD.ipaddr IS NOT NULL AND NEW.ipaddr IS NOT NULL AND NEW.ipaddr != ''
	DO INSTEAD
	UPDATE cc_ast_instance SET ipaddr = NEW.ipaddr, port = NEW.port, regseconds = NEW.regseconds,
			username = NEW.username, fullcontact = NEW.fullcontact, regserver = NEW.regserver,
			useragent = NEW.useragent
		WHERE userid = OLD.realtime_id
		  AND dyn = true
		AND srvid = ( SELECT id from cc_a2b_server WHERE db_username = current_user);
	
-- Remove the instance entry. TODO: wouldn't it be better to log the old ip?
CREATE OR REPLACE RULE realtime_sip_update_rd AS ON UPDATE TO realtime_sip_peers
	WHERE OLD.ipaddr IS NOT NULL AND (NEW.ipaddr IS NULL OR NEW.ipaddr = '0.0.0.0'
		OR NEW.ipaddr = '')
	DO INSTEAD
	DELETE FROM cc_ast_instance
		WHERE userid = OLD.realtime_id
		  AND dyn = true
		AND srvid = ( SELECT id from cc_a2b_server WHERE db_username = current_user);


GRANT all ON realtime_sip_peers TO a2b_group ;

CREATE OR REPLACE VIEW static_sip_peers AS
SELECT cc_ast_instance.srvid,
	COALESCE(cc_card.username, cc_booth.peername) AS name,
	(CASE WHEN cc_card.id IS NOT NULL THEN 'card:' || cc_card.id::TEXT
		WHEN cc_booth.id IS NOT NULL THEN 'booth:' || cc_booth.id::TEXT
		ELSE '' END) AS accountcode,
	COALESCE(cc_card.userpass, cc_booth.peerpass) AS secret,
	cc_ast_users.id AS realtime_id,
	"type", "context", videosupport, fromdomain, amaflags, dtmfmode,
	defaultip, fromuser, host,
	progressinband, incominglimit, outgoinglimit,
	nat, canreinvite, insecure,
	rtpkeepalive, rtpholdtimeout, rtptimeout,
	qualify,
	permit, deny, mask,
	disallow, allow,
	cancallforward, musiconhold, setvar,
	ipaddr, COALESCE(port, defport) AS port, regseconds,
	cc_ast_instance.username, fullcontact, regserver
	
	FROM cc_ast_users_config, cc_ast_users
		LEFT JOIN cc_ast_instance ON (cc_ast_instance.userid = cc_ast_users.id 
			AND cc_ast_instance.sipiax = 1
			AND cc_ast_instance.dyn = false )
		LEFT JOIN cc_card ON (cc_ast_users.card_id = cc_card.id)
		LEFT JOIN cc_booth ON (cc_ast_users.booth_id = cc_booth.id)
	WHERE cc_ast_users_config.id = cc_ast_users.config
		AND cc_ast_users.has_sip = true
	;

CREATE OR REPLACE FUNCTION sip_create_static_peers(s_card_grp INTEGER, s_srvid INTEGER,
	do_sip BOOLEAN, do_iax BOOLEAN) RETURNS void AS $$
BEGIN
	INSERT INTO cc_ast_instance(userid,srvid,sipiax,dyn)
		SELECT cc_ast_users.id,s_srvid,1,false
		  FROM cc_ast_users, cc_card
		  WHERE cc_ast_users.card_id = cc_card.id
		    AND cc_card.grp = s_card_grp
		    AND cc_ast_users.has_sip = true AND do_sip = true;
	INSERT INTO cc_ast_instance(userid,srvid,sipiax,dyn)
		SELECT cc_ast_users.id,s_srvid,2,false
		  FROM cc_ast_users, cc_card
		  WHERE cc_ast_users.card_id = cc_card.id
		    AND cc_card.grp = s_card_grp
		    AND cc_ast_users.has_iax = true AND do_iax = true;
END; $$ LANGUAGE plpgsql STRICT VOLATILE;

CREATE OR REPLACE FUNCTION sip_update_static_peers(s_agentid INTEGER, s_srvid INTEGER,
	do_sip BOOLEAN, do_iax BOOLEAN) RETURNS void AS $$
BEGIN
	PERFORM srvid FROM cc_ast_instance WHERE srvid = s_srvid AND dyn = true;
	IF FOUND THEN
		RAISE WARNING 'Server % should not have both static and dynamic entries, you are asking for trouble!', s_srvid;
	END IF;
	
	-- First, clear all previous instances for that server
	DELETE FROM cc_ast_instance 
		WHERE srvid = s_srvid AND dyn = false;
	
	-- Create static peers for regular users (agentrole: 2)
	PERFORM sip_create_static_peers(id, s_srvid,do_sip, do_iax) FROM
		cc_card_group WHERE agentid = s_agentid AND agent_role = 2;
	
	-- Create static peers for booths of agent
	INSERT INTO cc_ast_instance(userid,srvid,sipiax,dyn)
		SELECT cc_ast_users.id,s_srvid,1,false
		  FROM cc_ast_users, cc_booth
		  WHERE cc_ast_users.booth_id = cc_booth.id
		    AND cc_booth.agentid = s_agentid
		    AND cc_ast_users.has_sip = true AND do_sip = true;

	INSERT INTO cc_ast_instance(userid,srvid,sipiax,dyn)
		SELECT cc_ast_users.id,s_srvid,2,false
		  FROM cc_ast_users, cc_booth
		  WHERE cc_ast_users.booth_id = cc_booth.id
		    AND cc_booth.agentid = s_agentid
		    AND cc_ast_users.has_iax = true AND do_iax = true;
END; $$ LANGUAGE plpgsql STRICT VOLATILE;


CREATE OR REPLACE VIEW static_dplan_v AS
	SELECT cc_a2b_server.id AS srvid, cc_a2b_server.grp AS srvgrp,
		cc_a2b_server.host AS srv_host,
		COALESCE(cc_booth.peername, cc_card.username) AS peername,
		cc_numplan.id AS nplan, cc_numplan.name AS npname,
		cc_card.useralias, cc_ast_instance.sipiax
	  FROM cc_a2b_server, cc_ast_instance, cc_ast_users
		LEFT JOIN cc_booth ON (cc_ast_users.booth_id = cc_booth.id)
		LEFT JOIN cc_card ON (cc_booth.def_card_id = cc_card.id OR cc_ast_users.card_id = cc_card.id)
	  	FULL JOIN cc_card_group ON (cc_card.grp = cc_card_group.id)
	  	FULL JOIN cc_numplan ON (cc_card_group.numplan = cc_numplan.id)
	  WHERE cc_ast_instance.srvid = cc_a2b_server.id AND cc_ast_instance.userid = cc_ast_users.id
	    AND cc_ast_instance.dyn = false
	ORDER BY cc_numplan.id, cc_card.useralias;
--eof
