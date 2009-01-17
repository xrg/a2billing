
-- Function: gen_vouchers(s_crdgrp int4, s_serial bool, s_num int4, s_start text, s_voucherlen int4, s_tag text, s_credit "numeric", s_activated bool)

-- DROP FUNCTION gen_vouchers(s_crdgrp int4, s_serial bool, s_num int4, s_start text, s_voucherlen int4, s_tag text, s_credit "numeric", s_activated bool);

CREATE OR REPLACE FUNCTION gen_vouchers(s_crdgrp int4, s_serial bool, s_num int4, s_start text, s_voucherlen int4, s_tag text, s_credit "numeric", s_activated bool)
  RETURNS int4 AS
$BODY$
DECLARE
	dloop INTEGER;
	dremain INTEGER;
	planrow RECORD;
	din	INTEGER;
	dstart  BIGINT;
BEGIN
	dloop:=0;
	dremain :=s_num;
	

	SELECT cc_card_group.id AS grp, def_currency
		INTO planrow
		FROM cc_card_group WHERE cc_card_group.id = s_crdgrp;
	IF NOT FOUND THEN
		RAISE EXCEPTION 'Card group not found!';
	END IF;

	RAISE NOTICE 'Row: %',planrow;
	--
	dstart:= s_start::BIGINT;

	LOOP
		RAISE NOTICE 'Loop %',dloop;
		--
		IF dloop > 100 THEN
			RAISE EXCEPTION 'Cannot find usable voucher';
		END IF;
		
		dloop := dloop + 1;

		IF s_serial THEN
		     INSERT INTO vouchers (expirationdate, card_grp, voucher, tag, credit, activated)
		     SELECT current_timestamp + interval '6 month', planrow.grp, foo.voucher, s_tag, s_credit, s_activated
			FROM ( SELECT gen_servouchers(planrow.grp, dremain, 
					dstart,s_voucherlen) AS voucher) AS foo;

		    GET DIAGNOSTICS din = ROW_COUNT;
		    RAISE NOTICE 'Din : %, dremain: %',din,dremain;
			--
		    dstart := dstart + dremain;
		    dremain :=dremain - din;
		
		ELSE
		    INSERT INTO vouchers (expirationdate, card_grp, voucher, tag, credit, activated)
		     SELECT current_timestamp + interval '6 month', planrow.grp, foo.voucher, s_tag, s_credit, s_activated
			FROM ( SELECT gen_rndvouchers(planrow.grp, dremain, 
					dstart,s_voucherlen) AS voucher) AS foo;
			
		    GET DIAGNOSTICS din = ROW_COUNT;
		    RAISE NOTICE 'Din : %, dremain: %',din,dremain;
			--
		    dremain :=dremain - din;
		END IF;
		
		EXIT WHEN dremain <= 0 ;
	END LOOP;
	
	RETURN s_num - dremain;
END; $BODY$
  LANGUAGE 'plpgsql' VOLATILE;
ALTER FUNCTION gen_vouchers(s_crdgrp int4, s_serial bool, s_num int4, s_start text, s_voucherlen int4, s_tag text, s_credit "numeric", s_activated bool) OWNER TO a2billing;




-- Function: gen_servouchers(s_crdgrp int4, s_num int4, s_start int8, s_len int4)

-- DROP FUNCTION gen_servouchers(s_crdgrp int4, s_num int4, s_start int8, s_len int4);

CREATE OR REPLACE FUNCTION gen_servouchers(s_crdgrp int4, s_num int4, s_start int8, s_len int4)
  RETURNS SETOF text AS
$BODY$
	SELECT foo.ser
		FROM ( SELECT lpad(generate_series($3,$3+$2-1)::TEXT,$4,'0') AS ser) AS foo
		WHERE foo.ser NOT IN ( SELECT voucher FROM vouchers
					WHERE vouchers.card_grp = $1 );
$BODY$
  LANGUAGE 'sql' VOLATILE STRICT;


-- Function: gen_rndvouchers(s_crdgrp int4, s_num int4, s_start int8, s_len int4)

-- DROP FUNCTION gen_rndvouchers(s_crdgrp int4, s_num int4, s_start int8, s_len int4);

CREATE OR REPLACE FUNCTION gen_rndvouchers(s_crdgrp int4, s_num int4, s_start int8, s_len int4)
  RETURNS SETOF text AS
$BODY$
	SELECT DISTINCT foo.rnd
		FROM ( SELECT generate_series(1,$2) AS ser, mknumpasswd($4) AS rnd) AS foo
		WHERE foo.rnd NOT IN ( SELECT voucher FROM vouchers
					WHERE vouchers.card_grp = $1 );
$BODY$
  LANGUAGE 'sql' VOLATILE STRICT;


