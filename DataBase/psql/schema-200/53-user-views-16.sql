/* Realtime views for asterisk 1.6 */

/*-* temp: */
DROP VIEW IF EXISTS realtime16_sip_peers;
DROP VIEW IF EXISTS realtime16_sip_regs;


CREATE OR REPLACE VIEW realtime16_sip_peers AS
SELECT COALESCE(cc_ast_users.peernameb,cc_card.username, cc_booth.peername) AS name,
	(CASE WHEN cc_card.id IS NOT NULL THEN 'card:' || cc_card.id::TEXT
		WHEN cc_booth.id IS NOT NULL THEN 'booth:' || cc_booth.id::TEXT
		ELSE '' END) AS accountcode,
	COALESCE(cc_ast_users.secretb,cc_card.userpass, cc_booth.peerpass) AS secret,
	'"' || COALESCE(cc_ast_users.callerid,( COALESCE(substr(cc_card.firstname,1,1)||'. ', '') || cc_card.lastname),
		cc_booth.callerid, cc_booth.name) || '" < >' AS callerid,
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
	cc_card.username AS mailbox,
	COALESCE(cc_ast_users.callgroup, cc_ast_users_config.callgroup) AS callgroup,
	COALESCE(cc_ast_users.pickupgroup, cc_ast_users_config.pickupgroup) AS pickupgroup
	
	FROM cc_ast_users_config, cc_ast_users
		LEFT JOIN cc_card ON (cc_ast_users.card_id = cc_card.id AND cc_card.status <> 0)
		LEFT JOIN cc_booth ON (cc_ast_users.booth_id = cc_booth.id)
	
	WHERE cc_ast_users_config.id = cc_ast_users.config
		AND cc_ast_users.has_sip = true
	;

/* From asterisk, the code of realtime16_sip_regs should be:
	name		VARCHAR(10+)
	ipaddr		VARCHAR(15)
	port		UINT16
	regseconds	UINT16
	defaultuser	VARCHAR, 10,
	fullcontact	VARCHAR, 35,
	regserver	VARCHAR, 20,
	useragent	VARCHAR, 20,
	
	.. but it can contain any fields, which will be merged with realtime_sip_peers
	upon build_peer.
*/

CREATE OR REPLACE VIEW realtime16_sip_regs AS
SELECT COALESCE(cc_ast_users.peernameb,cc_card.username, cc_booth.peername) AS name,
	cc_ast_users.id AS realtime_id,
	ipaddr , port, regseconds,
	cc_ast_instance.username AS defaultuser, COALESCE(fullcontact,'')::varchar(80) AS fullcontact, 
	regserver, useragent,
	(CASE WHEN cc_ast_instance.sipiax = 5 THEN 's' ELSE NULL END) AS callbackextension
	
	FROM cc_ast_users
		LEFT JOIN cc_ast_instance ON (cc_ast_instance.userid = cc_ast_users.id 
			AND (cc_ast_instance.sipiax = 1 OR cc_ast_instance.sipiax = 5)
			AND cc_ast_instance.dyn = true
			AND cc_ast_instance.srvid = ( SELECT id from cc_a2b_server 
				WHERE db_username = current_user))
		LEFT JOIN cc_card ON (cc_ast_users.card_id = cc_card.id AND cc_card.status <> 0)
		LEFT JOIN cc_booth ON (cc_ast_users.booth_id = cc_booth.id)
	WHERE cc_ast_users.has_sip = true ;


CREATE OR REPLACE RULE realtime16_sip_update_rn AS ON UPDATE TO realtime16_sip_regs
	DO INSTEAD NOTHING;

-- First case: registration of a sip peer: insert the instance
CREATE OR REPLACE RULE realtime16_sip_update_ri AS ON UPDATE TO realtime16_sip_regs
	WHERE OLD.ipaddr IS NULL AND NULLIF(NEW.ipaddr,'') IS NOT NULL
	DO INSTEAD
	INSERT INTO cc_ast_instance(userid, srvid, dyn,sipiax,ipaddr,port, regseconds,
			username, fullcontact, regserver, useragent)
		VALUES(NEW.realtime_id, ( SELECT id from cc_a2b_server WHERE db_username = current_user),
			true,1,NEW.ipaddr,NEW.port,NEW.regseconds,NEW.defaultuser,NEW.fullcontact,NEW.regserver, NEW.useragent);

CREATE OR REPLACE RULE realtime16_sip_update_r3 AS ON UPDATE TO realtime16_sip_regs
	WHERE OLD.ipaddr IS NOT NULL AND NULLIF(NEW.ipaddr,'') IS NOT NULL
	DO INSTEAD
	UPDATE cc_ast_instance SET ipaddr = NEW.ipaddr, port = NEW.port, regseconds = NEW.regseconds,
			username = NEW.defaultuser, fullcontact = NEW.fullcontact, regserver = NEW.regserver,
			useragent = NEW.useragent
		WHERE userid = OLD.realtime_id
		  AND dyn = true
		AND srvid = ( SELECT id from cc_a2b_server WHERE db_username = current_user);
	
-- Remove the instance entry. TODO: wouldn't it be better to log the old ip?
CREATE OR REPLACE RULE realtime16_sip_update_rd AS ON UPDATE TO realtime16_sip_regs
	WHERE OLD.ipaddr IS NOT NULL AND NULLIF(NEW.ipaddr,'') IS NULL
	DO INSTEAD
	DELETE FROM cc_ast_instance
		WHERE userid = OLD.realtime_id
		  AND dyn = true
		AND srvid = ( SELECT id from cc_a2b_server WHERE db_username = current_user);



CREATE OR REPLACE VIEW realtime16_sip_regstates AS
SELECT COALESCE(cc_ast_users.peernameb,cc_card.username, cc_booth.peername) AS name,
	cc_ast_instance.srvid,
	cc_ast_users.id AS realtime_id,
	cc_ast_users.host,
	ipaddr, port,
	cc_ast_instance.username AS defaultuser,
	regserver, useragent,
	cc_ast_instance.sipiax,
	cc_ast_instance.reg_state
	
	FROM cc_ast_users
		LEFT JOIN cc_ast_instance ON (cc_ast_instance.userid = cc_ast_users.id 
			AND (/*cc_ast_instance.sipiax = 1 OR*/ cc_ast_instance.sipiax = 5)
			AND cc_ast_instance.dyn = true )
		LEFT JOIN cc_card ON (cc_ast_users.card_id = cc_card.id AND cc_card.status <> 0)
		LEFT JOIN cc_booth ON (cc_ast_users.booth_id = cc_booth.id)
	WHERE cc_ast_users.has_sip = true ;


GRANT all ON realtime16_sip_peers TO a2b_group ;
GRANT all ON realtime16_sip_regs TO a2b_group ;


-- eof
