-- Copyright P.Christeas 2006


-- misc stuff for testing..




CREATE OR REPLACE FUNCTION pay_session( sid bigint, agentid_p bigint, do_close boolean, do_carry boolean) RETURNS NUMERIC
	AS $$
	DECLARE
		ssum NUMERIC;
		cid bigint;
	BEGIN
		SELECT cc_card.credit, cc_card.id INTO ssum, cid FROM cc_card, cc_shopsessions, cc_agent_cards
			WHERE cc_card.id = cc_shopsessions.card AND
				cc_agent_cards.card_id = cc_card.id AND cc_agent_cards.agentid = agentid_p AND
				cc_shopsessions.id = sid ;
		IF NOT FOUND THEN
			RAISE EXCEPTION 'No such session for agent';
		END IF;
		INSERT INTO cc_agentrefill(card_id, agentid, credit, carried)
			VALUES(cid, agentid_p,0-ssum, do_carry);
		IF do_close THEN
			UPDATE cc_shopsessions SET endtime = now() WHERE
				card = cid AND id = sid;
		END IF;
	RETURN ssum;
	END; $$
	LANGUAGE plpgsql STRICT;


CREATE OR REPLACE VIEW cc_closed_sessions AS
	SELECT cc_shopsessions.id AS sid, cc_shopsessions.card, (SUM(cc_session_invoice.pos_charge) - SUM(cc_session_invoice.neg_charge)) AS ssum
		FROM cc_shopsessions, cc_session_invoice WHERE
		cc_shopsessions.endtime IS NOT NULL AND
		cc_shopsessions.id = cc_session_invoice.sid 
		GROUP by cc_shopsessions.id,cc_shopsessions.card;

CREATE OR REPLACE VIEW cc_session_problems AS
	SELECT cc_closed_sessions.sid, cc_closed_sessions.card, cc_agent_cards.agentid, cc_agent_cards.def, 'Imbalance'::text AS Problem
		FROM  cc_closed_sessions, cc_agent_cards WHERE
			cc_agent_cards.card_id = cc_closed_sessions.card
			AND cc_closed_sessions.ssum <> 0 ;
			
-- One view for all: have all the session transactions in one table.
--eof