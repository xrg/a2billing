-- Extra stuff on cc_card


/** Card dynamic view: transparently apply all logic on cc_card
    This view should behave like cc_card, but additionally apply
    rules (as triggers).
*/

CREATE OR REPLACE VIEW cc_card_dv AS
	SELECT cc_card.*, 
		name AS group_name, agentid, simultaccess, typepaid, numplan,
		tariffgroup, def_currency, voipcall, vat, initialbalance,
		invoiceday, expiretype, expiredays, autorefill, agent_role
	FROM cc_card, cc_card_group
	WHERE cc_card.grp = cc_card_group.id;

GRANT SELECT ON cc_card_dv TO a2b_group;

--eof
