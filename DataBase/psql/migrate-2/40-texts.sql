-- Migrate texts

-- They are a little tricky: old texts have to be merged to new ones..

DELETE FROM cc_texts;
DELETE FROM cc_paytypes;

INSERT INTO cc_texts ( id, lang, txt, src)
	SELECT id,lang,txt,src
	FROM a2b_old.cc_texts;

INSERT INTO cc_paytypes (id, side, charge, preset)
	SELECT id, side, charge, preset
	FROM a2b_old.cc_paytypes ;


--eof

