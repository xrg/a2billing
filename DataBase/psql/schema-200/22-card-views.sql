-- Extra stuff on cc_card


/** Card dynamic view: transparently apply all logic on cc_card
    This view should behave like cc_card, but additionally apply
    rules (as triggers).
*/

CREATE OR REPLACE VIEW cc_card_dv AS
	SELECT * FROM cc_card;
