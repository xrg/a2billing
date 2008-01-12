-- All the functionality of realtime users/peers is done through
-- views, that combine several tables.

-- The use of views actually helps prevent * from updating the wrong
-- fields.


CREATE OR REPLACE VIEW realtime_sip_peers AS
SELECT COALESCE(cc_card.username, cc_booth.peername) AS name,
	(CASE WHEN cc_card.id IS NOT NULL THEN 'card:' || cc_card.id::TEXT
		WHEN cc_booth.id IS NOT NULL THEN 'booth:' || cc_booth.id::TEXT
		ELSE '' END) AS accountcode,
	COALESCE(cc_card.userpass, cc_booth.peerpass) AS secret,
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
	ipaddr, COALESCE(port, defport) AS port, regseconds,
	cc_ast_instance.username, fullcontact, regserver
	
	FROM cc_ast_users_config, cc_ast_users
		LEFT JOIN cc_ast_instance ON (cc_ast_instance.userid = cc_ast_users.id 
			AND cc_ast_instance.sipiax = 1
			AND cc_ast_instance.dyn = true
			AND cc_ast_instance.srvid = ( SELECT id from cc_a2b_server 
				WHERE db_username = current_user))
		LEFT JOIN cc_card ON (cc_ast_users.card_id = cc_card.id)
		LEFT JOIN cc_booth ON (cc_ast_users.booth_id = cc_booth.id)
	
	WHERE cc_ast_users_config.id = cc_ast_users.config
		AND cc_ast_users.has_sip = true
		
	;
	
-- TODO: Disabled peers!


-- LEFT OUTER JOIN cc_ast_instance ON cau.id = cc_ast_instance.userid


--- Now, define update rules for the views. That way, asterisk will be
-- able to update *only some* of the information, such as where the user
-- is actually registered.
CREATE OR REPLACE RULE realtime_sip_update_rn AS ON UPDATE TO realtime_sip_peers
	DO INSTEAD NOTHING;

-- First case: registration of a sip peer: insert the instance
CREATE OR REPLACE RULE realtime_sip_update_ri AS ON UPDATE TO realtime_sip_peers
	WHERE OLD.ipaddr IS NULL AND NEW.ipaddr IS NOT NULL
	DO INSTEAD
	INSERT INTO cc_ast_instance(userid, srvid, dyn,sipiax,ipaddr,port, regseconds,
			username, fullcontact, regserver)
		VALUES(NEW.realtime_id, ( SELECT id from cc_a2b_server WHERE db_username = current_user),
			true,1,NEW.ipaddr,NEW.port,NEW.regseconds,NEW.username,NEW.fullcontact,NEW.regserver);

CREATE OR REPLACE RULE realtime_sip_update_r3 AS ON UPDATE TO realtime_sip_peers
	WHERE OLD.ipaddr IS NOT NULL AND NEW.ipaddr IS NOT NULL
	DO INSTEAD
	UPDATE cc_ast_instance SET ipaddr = NEW.ipaddr, port = NEW.port, regseconds = NEW.regseconds,
			username = NEW.username, fullcontact = NEW.fullcontact, regserver = NEW.regserver
		WHERE userid = OLD.realtime_id
		  AND dyn = true
		AND srvid = ( SELECT id from cc_a2b_server WHERE db_username = current_user);
	
-- Remove the instance entry. TODO: wouldn't it be better to log the old ip?
CREATE OR REPLACE RULE realtime_sip_update_rd AS ON UPDATE TO realtime_sip_peers
	WHERE OLD.ipaddr IS NOT NULL AND (NEW.ipaddr IS NULL OR NEW.ipaddr = '0.0.0.0')
	DO INSTEAD
	DELETE FROM cc_ast_instance
		WHERE userid = OLD.realtime_id
		  AND dyn = true
		AND srvid = ( SELECT id from cc_a2b_server WHERE db_username = current_user);


GRANT all ON realtime_sip_peers TO a2b_group ;

--eof
