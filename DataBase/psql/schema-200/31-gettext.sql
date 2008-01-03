-- Gettext
-- Convert some text from 'C' to another language..

CREATE OR REPLACE FUNCTION gettext( ptxt TEXT, plang VARCHAR(10)) RETURNS TEXT AS $$
DECLARE
	res TEXT;
BEGIN
	IF (plang = 'C' ) OR (plang = 'en') THEN
		RETURN ptxt;
	END IF;
	SELECT cc_texts.txt INTO res FROM cc_texts 
		WHERE cc_texts.lang = plang AND cc_texts.id IN 
			(SELECT id FROM cc_texts WHERE cc_texts.lang = 'C' AND cc_texts.txt = ptxt);
	IF FOUND THEN
		RETURN res;
	ELSE
		RETURN ptxt;
	END IF;
END; $$ LANGUAGE plpgsql STRICT STABLE;

CREATE OR REPLACE FUNCTION gettexti( pid INTEGER, plang VARCHAR(10)) RETURNS TEXT AS $$
DECLARE
	res TEXT;
BEGIN
	SELECT cc_texts.txt INTO res FROM cc_texts 
		WHERE cc_texts.lang = plang AND cc_texts.id  = pid;
	IF FOUND THEN
		RETURN res;
	END IF;
	SELECT cc_texts.txt INTO res FROM cc_texts
		WHERE cc_texts.lang = 'C' AND cc_texts.id = pid;
	RETURN res;
END; $$ LANGUAGE plpgsql STRICT STABLE;

-- The opposite of gettext! Insert some new text into the database and get its id..
-- The text MUST be in English = 'C' and this function should always return a valid
-- id as long as ptxt IS NOT NULL..
CREATE OR REPLACE FUNCTION gettext_ri(ptxt TEXT) returns integer AS $$
	DECLARE res integer;
BEGIN
	SELECT id INTO res FROM cc_texts WHERE lang = 'C' AND txt = ptxt;
	IF FOUND THEN RETURN res; END IF;
	
	SELECT INTO res MAX(id) FROM cc_texts;
	IF res IS NULL THEN res := 0; END IF;
	res := res + 1;
	
	INSERT INTO cc_texts(id,txt,lang,src) VALUES(res,ptxt,'C',2);
	RETURN res;
END; $$ LANGUAGE plpgsql STRICT VOLATILE;

-- Optimized version of gettext_r.
-- Note: this function may be faster, but will raise an exception for non-existent
-- strings..

CREATE OR REPLACE FUNCTION gettext_r(ptxt TEXT) returns integer AS $$
	DECLARE res integer;
BEGIN
	SELECT id INTO res FROM cc_texts WHERE lang = 'C' AND txt = ptxt;
	IF NOT FOUND THEN 
		RAISE EXCEPTION 'Text ''%'' not found in database!',ptxt;
	END IF;
	
	RETURN res; 
END; $$ LANGUAGE plpgsql STRICT STABLE;


CREATE OR REPLACE FUNCTION gettext_add_missing(lang VARCHAR(10)) RETURNS void AS  $$
	INSERT INTO cc_texts (id, txt, src, lang) SELECT id, txt, 0 AS src, $1 AS lang FROM cc_texts 
		WHERE lang = 'C' AND  id NOT IN (SELECT id FROM cc_texts WHERE lang = $1 );
$$ LANGUAGE SQL STRICT;
