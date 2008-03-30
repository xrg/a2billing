-- Extra stuff on cc_card


/** Card dynamic view: transparently apply all logic on cc_card
    This view should behave like cc_card, but additionally apply
    rules (as triggers).
*/

CREATE OR REPLACE VIEW cc_card_features AS
	SELECT cs.card AS card_id, into_array(sft.feature) AS features
	    FROM card_subscription AS cs, subscription_feature_templ AS sft 
	    WHERE cs.template = sft.id
		AND cs.status=1 AND cs.activedate <= now() AND cs.expiredate > now()
	    GROUP BY cs.card;

CREATE OR REPLACE VIEW cc_card_dv AS
	SELECT cc_card.*, 
		name AS group_name, agentid, simultaccess, typepaid, numplan,
		tariffgroup, def_currency, voipcall, vat, initiallimit,
		invoiceday, expiretype, expiredays, autorefill, agent_role,
		cc_card_features.features
	FROM cc_card_group, cc_card
		LEFT JOIN cc_card_features ON (cc_card.id = cc_card_features.card_id)
	WHERE cc_card.grp = cc_card_group.id;

-- TODO: rules on cc_card_dv to expire cards..

GRANT SELECT ON cc_card_dv TO a2b_group;

--eof
