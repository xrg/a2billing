-- Rules on cc_agentpay

CREATE OR REPLACE FUNCTION cc_agentpay_it() RETURNS trigger AS $$
BEGIN
	UPDATE cc_agent SET credit = credit + NEW.credit WHERE id = NEW.agentid;
	IF NOT FOUND THEN
		RAISE EXCEPTION 'Failed to update agent''s credit';
	END IF;
	IF NEW.invoice_id IS NOT NULL THEN
		UPDATE cc_invoices SET payment_status = (CASE WHEN payment_status = 0 THEN 3 
			WHEN payment_status = 1 THEN 2 ELSE payment_status END), payment_date = NEW.date
			WHERE id = NEW.invoice_id AND agentid = NEW.agentid;
		IF NOT FOUND THEN
			RAISE WARNING 'Invoice specified, but couldn''t be marked as paid.';
		END IF;
	END IF;
	RETURN NEW;
END ; $$ LANGUAGE plpgsql STRICT;

CREATE OR REPLACE FUNCTION cc_agentpay_itu() RETURNS trigger AS $$
BEGIN
	IF NEW.agentid <> OLD.agentid THEN
		RAISE EXCEPTION 'Change of agents for payments is forbidden!';
	END IF;
	UPDATE cc_agent SET credit = credit + NEW.credit - OLD.credit WHERE id = NEW.agentid;
	IF NOT FOUND THEN
		RAISE EXCEPTION 'Failed to update agent''s credit';
	END IF;
	RETURN NEW;
END ; $$ LANGUAGE plpgsql STRICT;

CREATE OR REPLACE FUNCTION cc_agentpay_itd() RETURNS trigger AS $$
BEGIN
	UPDATE cc_agent SET credit = credit - OLD.credit WHERE id = OLD.agentid;
	IF NOT FOUND THEN
		RAISE EXCEPTION 'Failed to update agent''s credit';
	END IF;
	RETURN OLD;
END ; $$ LANGUAGE plpgsql STRICT;

DROP TRIGGER IF EXISTS cc_agent_pay_it ON cc_agentpay;
DROP TRIGGER IF EXISTS cc_agent_pay_itd ON cc_agentpay;
DROP TRIGGER IF EXISTS cc_agent_pay_itu ON cc_agentpay;
DROP TRIGGER IF EXISTS cc_apay_check_invoice ON cc_agentpay;

CREATE TRIGGER cc_agent_pay_it BEFORE INSERT ON cc_agentpay
	FOR EACH ROW EXECUTE PROCEDURE cc_agentpay_it();
CREATE TRIGGER cc_agent_pay_itu BEFORE UPDATE ON cc_agentpay
	FOR EACH ROW EXECUTE PROCEDURE cc_agentpay_itu();
CREATE TRIGGER cc_agent_pay_itd BEFORE DELETE ON cc_agentpay
	FOR EACH ROW EXECUTE PROCEDURE cc_agentpay_itd();

CREATE TRIGGER cc_apay_check_invoice BEFORE UPDATE OR DELETE ON cc_agentpay
	FOR EACH ROW EXECUTE PROCEDURE cc_invoice_lock_f();

--eof
