-- Currency related functions

-- All the functions are related to the 'base currency', as implied in cc_currencies

CREATE OR REPLACE FUNCTION format_currency(money_sum NUMERIC, to_cur CHAR(3)) RETURNS text
	AS $$
	SELECT CASE WHEN sign_pre THEN 
			csign || ' ' || to_char( $1 / to_rate, cformat)
		ELSE
			to_char( $1 / to_rate, cformat) || ' ' || csign
		END
	FROM (SELECT "value" AS to_rate, cformat,
			COALESCE(csign,currency) AS csign , sign_pre 
		FROM cc_currencies
		WHERE currency = $2) AS foo
		;
	$$
	LANGUAGE SQL STABLE STRICT;
	
/*WHEN abs(($1 * from_rate) / to_rate) <= 0.10 AND sign_pre THEN
			csign || 'c ' || to_char( ($1 * from_rate*100.0) / to_rate, cformat)*/
		
CREATE OR REPLACE FUNCTION format_currency2(money_sum NUMERIC, to_cur CHAR(3)) RETURNS text
	AS $$
	SELECT CASE WHEN sign_pre THEN 
			csign || ' ' || to_char( $1 / to_rate, cformat2)
		ELSE
			to_char( $1 / to_rate, cformat2) || ' ' || csign
		END
	FROM (SELECT "value" AS to_rate, cformat2,
			COALESCE(csign,currency) AS csign , sign_pre 
		FROM cc_currencies
		WHERE currency = $2) AS foo
		;
$$ LANGUAGE SQL STABLE STRICT;


CREATE OR REPLACE FUNCTION conv_currency_to(money_sum NUMERIC, from_cur CHAR(3)) RETURNS NUMERIC
	AS $$
	SELECT  ($1 / value)
		FROM cc_currencies
		WHERE currency = $2 ;
	$$
	LANGUAGE SQL STABLE STRICT;

CREATE OR REPLACE FUNCTION conv_currency_from(money_sum NUMERIC, to_cur CHAR(3)) RETURNS NUMERIC
	AS $$
	SELECT  ($1 * value)
		FROM cc_currencies
		WHERE currency = $2 ;
	$$
	LANGUAGE SQL STABLE STRICT;
