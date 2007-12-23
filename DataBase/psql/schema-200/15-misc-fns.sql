-- Miscelaneous functions

CREATE OR REPLACE FUNCTION mkpasswd(len INTEGER) RETURNS TEXT AS $$
DECLARE
	ret TEXT;
	rn INTEGER;
BEGIN
	IF len < 1 OR len > 20 THEN
		RAISE EXCEPTION 'Password length out of bounds!';
	END IF;
	
	ret := '';
	
	FOR i IN 1..len LOOP
		rn := floor(random() * 62.0);
		IF rn < 10 THEN
			ret := ret || chr(rn + 48) ;
		ELSIF rn < 36 THEN
			ret := ret || chr(rn + 55) ;
		ELSE
			ret := ret || chr(rn + 61) ;
		END IF;
	END LOOP;
	
	RETURN RET;
END; $$ LANGUAGE PLPGSQL STRICT VOLATILE;

CREATE OR REPLACE FUNCTION mknumpasswd(len INTEGER) RETURNS TEXT AS $$
DECLARE
	ret TEXT;
	rn INTEGER;
BEGIN
	IF len < 1 OR len > 20 THEN
		RAISE EXCEPTION 'Password length out of bounds!';
	END IF;
	
	ret := '';
	
	FOR i IN 1..len LOOP
		rn := floor(random() * 10.0);
		ret := ret || chr(rn + 48) ;
	END LOOP;
	
	RETURN RET;
END; $$ LANGUAGE PLPGSQL STRICT VOLATILE;

--eof
