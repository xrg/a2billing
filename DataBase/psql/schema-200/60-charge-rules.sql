-- Charge functions

CREATE TRIGGER cc_charge_check_invoice BEFORE UPDATE OR DELETE ON cc_card_charge
	FOR EACH ROW EXECUTE PROCEDURE cc_invoice_lock_f();

