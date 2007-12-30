-- Additional tables for callshop feature
-- Copyright (c) 2006 P.Christeas <p_christeas@yahoo.com>
--

/*CREATE TABLE cc_agent_cards (
	card_id bigint NOT NULL PRIMARY KEY REFERENCES cc_card(id) ON DELETE CASCADE,
	agentid bigint NOT NULL REFERENCES cc_agent(id) ON DELETE RESTRICT,
	def boolean NOT NULL DEFAULT 'f') ;*/
	



-- CREATE OR REPLACE FUNCTION booth_start(booth bigint, agent_id bigint) RETURNS bigint
-- 	AS $$
-- 		UPDATE cc_card SET activated= 't' 
-- 			FROM cc_agent, cc_booth 
-- 			WHERE cc_booth.cur_card_id= cc_card.id AND
-- 				cc_booth.id = $1 AND
-- 				cc_booth.agentid = $2;
-- 		select COUNT(cc_card.id) FROM cc_card,cc_agent, cc_booth 
-- 			WHERE cc_booth.cur_card_id= cc_card.id AND
-- 				cc_booth.id = $1 AND
-- 				cc_booth.agentid = $2;
-- 	$$ LANGUAGE SQL VOLATILE STRICT;
	
-- Charges could now come from the agents. If so, a a2b admin should better
-- confirm those.
	

-- eof
