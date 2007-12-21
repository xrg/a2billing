-- Views for realtime peer dialing


/* There is no need to have all modes in one view (yet),
   since it'd be better to iterate over separate trunks for
   each mode
*/

/* However, it is nice to associate useraliases with cc_card in one go.. */
CREATE OR REPLACE VIEW cc_alias2card_v AS
    SELECT cc_ast_instance.*, CASE WHEN cc_ast_users.card_id IS NOT NULL THEN 'card' 
	WHEN cc_ast_users.booth_id IS NOT NULL AND cc_booth.cur_card_id IS NOT NULL THEN 'booth-card'
		WHEN cc_ast_users.booth_id IS NOT NULL AND cc_booth.def_card_id IS NOT NULL THEN 'booth-def'
		WHEN cc_ast_users.booth_id IS NOT NULL THEN 'booth' END AS match_mode,
	cc_card.id AS card_id, cc_card.useralias, cc_card.grp AS card_grp,COALESCE(cc_booth.peername, cc_card.username) AS dialname,
	CASE WHEN sipiax = 1 THEN 'SIP'::TEXT WHEN sipiax = 2 THEN 'IAX2'::TEXT ELSE 'NONE'::TEXT END AS dialtech
	FROM cc_ast_instance,cc_ast_users LEFT JOIN cc_booth ON cc_ast_users.booth_id = cc_booth.id,
		cc_card
	WHERE cc_ast_instance.userid = cc_ast_users.id
		AND cc_card.id = COALESCE(cc_ast_users.card_id,cc_booth.cur_card_id,cc_booth.def_card_id) ;

CREATE OR REPLACE VIEW cc_dialpeer_local_v AS
	SELECT cc_alias2card_v.*, cc_card_group.numplan, cc_numplan.name AS numplan_name
	FROM cc_alias2card_v, cc_card_group, cc_a2b_server, cc_numplan
	WHERE cc_alias2card_v.card_grp = cc_card_group.id
	  AND cc_alias2card_v.srvid = cc_a2b_server.id
	  AND cc_a2b_server.db_username = current_user
	  AND cc_numplan.id = cc_card_group.numplan;
-- 
-- 
-- CREATE OR REPLACE VIEW cc_dialpeer_localbooth_v AS
-- 	SELECT cc_booth.peername AS dialip, CASE WHEN sipiax = 1 THEN 'SIP'::TEXT
-- 		WHEN sipiax = 2 THEN 'IAX2'::TEXT ELSE 'NONE'::TEXT END AS dialtech,
-- 		cc_card_group.numplan, cc_card.useralias
-- 	FROM cc_ast_instance,cc_booth, cc_ast_users, cc_card_group
-- 	WHERE cc_ast_instance.userid = cc_ast_users.id
-- 	  AND cc_ast_users.booth_id = cc_booth.id
-- 	  AND cc_card.grp = cc_card_group.id
-- 	  AND ;

GRANT SELECT ON cc_alias2card_v TO a2b_group;
GRANT SELECT ON cc_dialpeer_local_v TO a2b_group;
