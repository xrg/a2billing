-- Create mail function

CREATE OR REPLACE FUNCTION create_mail(p_templ VARCHAR(30), p_tomail TEXT, p_locale VARCHAR(10), p_params TEXT) RETURNS BIGINT
AS $$
DECLARE
	tmpl_id INTEGER;
	ml_id BIGINT;
BEGIN
	SELECT id INTO tmpl_id FROM cc_templatemail
		WHERE mtype = p_templ AND (lang = p_locale OR lang = 'C')
		ORDER BY (CASE WHEN lang = 'C' THEN 1 ELSE 0 END) ASC;
	IF NOT FOUND THEN
		RAISE WARNING 'No mail template for "%" found!',p_templ;
		RETURN NULL;
	END IF;
	
	INSERT INTO cc_mailings(tmail_id, tomail, args) VALUES( tmpl_id, p_tomail, p_params)
		RETURNING id INTO ml_id;
	RETURN ml_id;
END;
$$ LANGUAGE PLPGSQL VOLATILE;

-- eof
