--
\echo Migrating invoices

INSERT INTO cc_invoices ( id, cardid, agentid, orderref, created,
		cover_startdate, cover_enddate, amount, tax, total,
		invoicetype, filename, payment_date, payment_status )
	SELECT id, cardid, agentid, orderref, invoicecreated_date,
		cover_startdate, cover_enddate, amount, tax, total,
		invoicetype, filename, payment_date, payment_status
	FROM a2b_old.cc_invoices;

SELECT pg_catalog.setval('cc_invoices_id_seq', (SELECT last_value FROM a2b_old.cc_invoices_id_seq));
	

INSERT INTO cc_invoice_history ( id, invoiceid, idate, istatus)
	SELECT id, invoiceid,invoicesent_date, invoicestatus
	FROM a2b_old.cc_invoice_history ;
SELECT pg_catalog.setval('cc_invoice_history_id_seq', (SELECT last_value FROM a2b_old.cc_invoice_history_id_seq));

--eof
