-- Sip/IAX users

\echo Migrating SIP, IAX users
CREATE FUNCTION char2small(VARCHAR) RETURNS SMALLINT AS $$
	SELECT CASE WHEN $1 IS NULL THEN NULL
		WHEN $1 = '' THEN NULL
	 ELSE CAST( $1 AS SMALLINT) END;
$$ LANGUAGE SQL IMMUTABLE;

CREATE FUNCTION char2inet(VARCHAR) RETURNS INET AS $$
	SELECT CASE WHEN $1 IS NULL THEN NULL
		WHEN $1 = '' THEN NULL
		WHEN $1 SIMILAR TO '[0-9]+.[0-9]+.[0-9]+.[0-9]+' THEN CAST($1 AS INET)
		ELSE NULL END;
$$ LANGUAGE SQL IMMUTABLE;	

INSERT INTO cc_ast_users_config( cfg_name, "type", "context",
		fromdomain, amaflags, dtmfmode,
		nat, canreinvite, insecure,
		rtpholdtimeout, rtptimeout, qualify,
		permit, deny, mask, allow, disallow, cancallforward,
		musiconhold, setvar)
	SELECT 'Config SIP ' || MIN(id), "type", "context",
		fromdomain, amaflags, dtmfmode,
		nat, canreinvite, insecure,
		char2small(rtpholdtimeout), char2small(rtptimeout), qualify,
		permit, deny, mask, allow, disallow, cancallforward,
		musiconhold, setvar
	FROM a2b_old.cc_sip_buddies
	GROUP BY "type", "context", fromdomain, amaflags, dtmfmode,
		nat, canreinvite, insecure,
		rtptimeout, rtpholdtimeout, qualify,
		permit, deny, mask, allow, disallow, cancallforward,
		musiconhold, setvar ;

INSERT INTO cc_ast_users_config( cfg_name, "type", "context",
		fromdomain, amaflags, dtmfmode,
		nat, canreinvite, insecure,
		rtpholdtimeout, rtptimeout, qualify,
		permit, deny, mask, allow, disallow, cancallforward,
		musiconhold)
	SELECT 'Config IAX ' || MIN(id), "type", "context",
		fromdomain, amaflags, dtmfmode,
		nat, canreinvite, insecure,
		char2small(rtpholdtimeout), char2small(rtptimeout), qualify,
		permit, deny, mask, allow, disallow, cancallforward,
		musiconhold
	FROM a2b_old.cc_iax_buddies
	GROUP BY "type", "context", fromdomain, amaflags, dtmfmode,
		nat, canreinvite, insecure,
		rtpholdtimeout, rtptimeout, qualify,
		permit, deny, mask, allow, disallow, cancallforward,
		musiconhold;
 

INSERT INTO cc_ast_users (card_id, config, has_sip, has_iax, defaultip, fromuser, host)
	SELECT id_cc_card, config.id, true, false, char2inet( defaultip), fromuser, host
	FROM a2b_old.cc_sip_buddies, cc_ast_users_config AS config
	WHERE config.cfg_name LIKE 'Config SIP %'
		AND id_cc_card > 0
		AND cc_sip_buddies."type" IS NOT DISTINCT FROM config."type"
		AND cc_sip_buddies."context" IS NOT DISTINCT FROM config."context"
		AND cc_sip_buddies.fromdomain IS NOT DISTINCT FROM config.fromdomain
		AND cc_sip_buddies.amaflags IS NOT DISTINCT FROM config.amaflags
		AND cc_sip_buddies.dtmfmode IS NOT DISTINCT FROM config.dtmfmode
		AND cc_sip_buddies.nat IS NOT DISTINCT FROM config.nat
		AND cc_sip_buddies.canreinvite IS NOT DISTINCT FROM config.canreinvite
		AND cc_sip_buddies.insecure IS NOT DISTINCT FROM config.insecure
		AND char2small(cc_sip_buddies.rtpholdtimeout) IS NOT DISTINCT FROM config.rtpholdtimeout
		AND char2small(cc_sip_buddies.rtptimeout) IS NOT DISTINCT FROM config.rtptimeout
		AND cc_sip_buddies.qualify IS NOT DISTINCT FROM config.qualify
		AND cc_sip_buddies.permit IS NOT DISTINCT FROM config.permit
		AND cc_sip_buddies.deny IS NOT DISTINCT FROM config.deny
		AND cc_sip_buddies.mask IS NOT DISTINCT FROM config.mask
		AND cc_sip_buddies.allow IS NOT DISTINCT FROM config.allow
		AND cc_sip_buddies.disallow IS NOT DISTINCT FROM config.disallow
		AND cc_sip_buddies.cancallforward IS NOT DISTINCT FROM config.cancallforward
		AND cc_sip_buddies.musiconhold IS NOT DISTINCT FROM config.musiconhold
		AND cc_sip_buddies.setvar IS NOT DISTINCT FROM config.setvar;

INSERT INTO cc_ast_users (card_id, config, has_sip, has_iax, defaultip, fromuser, host)
	SELECT id_cc_card, config.id, false, true, char2inet(defaultip), fromuser, host
	FROM a2b_old.cc_iax_buddies, cc_ast_users_config AS config
	WHERE config.cfg_name LIKE 'Config IAX %'
		AND cc_iax_buddies."type" IS NOT DISTINCT FROM config."type"
		AND cc_iax_buddies."context" IS NOT DISTINCT FROM config."context"
		AND cc_iax_buddies.fromdomain IS NOT DISTINCT FROM config.fromdomain
		AND cc_iax_buddies.amaflags IS NOT DISTINCT FROM config.amaflags
		AND cc_iax_buddies.dtmfmode IS NOT DISTINCT FROM config.dtmfmode
		AND cc_iax_buddies.nat IS NOT DISTINCT FROM config.nat
		AND cc_iax_buddies.canreinvite IS NOT DISTINCT FROM config.canreinvite
		AND cc_iax_buddies.insecure IS NOT DISTINCT FROM config.insecure
		AND char2small(cc_iax_buddies.rtpholdtimeout) IS NOT DISTINCT FROM config.rtpholdtimeout
		AND char2small(cc_iax_buddies.rtptimeout) IS NOT DISTINCT FROM config.rtptimeout
		AND cc_iax_buddies.qualify IS NOT DISTINCT FROM config.qualify
		AND cc_iax_buddies.permit IS NOT DISTINCT FROM config.permit
		AND cc_iax_buddies.deny IS NOT DISTINCT FROM config.deny
		AND cc_iax_buddies.mask IS NOT DISTINCT FROM config.mask
		AND cc_iax_buddies.allow IS NOT DISTINCT FROM config.allow
		AND cc_iax_buddies.disallow IS NOT DISTINCT FROM config.disallow
		AND cc_iax_buddies.cancallforward IS NOT DISTINCT FROM config.cancallforward
		AND cc_iax_buddies.musiconhold IS NOT DISTINCT FROM config.musiconhold;

-- Booths
INSERT INTO cc_ast_users (booth_id, config, has_sip, has_iax, defaultip, fromuser, host)
	SELECT cc_booth.id, config.id, true, false, char2inet( defaultip), fromuser, host
	FROM a2b_old.cc_sip_buddies, cc_ast_users_config AS config, cc_booth
	WHERE config.cfg_name LIKE 'Config SIP %'
		AND (id_cc_card IS NULL OR id_cc_card = 0)
		AND cc_booth.peername = cc_sip_buddies.name
		AND cc_sip_buddies."type" IS NOT DISTINCT FROM config."type"
		AND cc_sip_buddies."context" IS NOT DISTINCT FROM config."context"
		AND cc_sip_buddies.fromdomain IS NOT DISTINCT FROM config.fromdomain
		AND cc_sip_buddies.amaflags IS NOT DISTINCT FROM config.amaflags
		AND cc_sip_buddies.dtmfmode IS NOT DISTINCT FROM config.dtmfmode
		AND cc_sip_buddies.nat IS NOT DISTINCT FROM config.nat
		AND cc_sip_buddies.canreinvite IS NOT DISTINCT FROM config.canreinvite
		AND cc_sip_buddies.insecure IS NOT DISTINCT FROM config.insecure
		AND char2small(cc_sip_buddies.rtpholdtimeout) IS NOT DISTINCT FROM config.rtpholdtimeout
		AND char2small(cc_sip_buddies.rtptimeout) IS NOT DISTINCT FROM config.rtptimeout
		AND cc_sip_buddies.qualify IS NOT DISTINCT FROM config.qualify
		AND cc_sip_buddies.permit IS NOT DISTINCT FROM config.permit
		AND cc_sip_buddies.deny IS NOT DISTINCT FROM config.deny
		AND cc_sip_buddies.mask IS NOT DISTINCT FROM config.mask
		AND cc_sip_buddies.allow IS NOT DISTINCT FROM config.allow
		AND cc_sip_buddies.disallow IS NOT DISTINCT FROM config.disallow
		AND cc_sip_buddies.cancallforward IS NOT DISTINCT FROM config.cancallforward
		AND cc_sip_buddies.musiconhold IS NOT DISTINCT FROM config.musiconhold
		AND cc_sip_buddies.setvar IS NOT DISTINCT FROM config.setvar;

DROP FUNCTION char2inet(VARCHAR);
DROP FUNCTION char2small(VARCHAR);
--eof
