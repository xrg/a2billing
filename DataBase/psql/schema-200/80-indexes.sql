-- Indexes

-- They are called last, so that all data has been inserted beforehand

CREATE INDEX cc_call_card_ind ON cc_call USING btree (cardid);
CREATE INDEX cc_call_starttime_ind ON cc_call USING btree (starttime);
CREATE INDEX cc_call_sid_ind ON cc_call USING btree (sessionid);
CREATE INDEX cc_call_uid_ind ON cc_call USING btree (uniqueid);
-- CREATE INDEX cc_call_terminatecause_ind ON cc_call USING btree (terminatecause); 	
-- CREATE INDEX cc_call_calledstation_ind ON cc_call USING btree (calledstation); 	

CREATE INDEX cc_card_grp ON cc_card(grp);

CREATE INDEX cc_card_creationdate_ind ON cc_card USING btree (creationdate);
CREATE INDEX cc_card_username_ind ON cc_card USING btree (username);
CREATE INDEX cc_card_useralias_ind ON cc_card (useralias);

CREATE INDEX ind_cc_invoices ON cc_invoices USING btree (cover_startdate);
CREATE INDEX ind_cc_invoice_history ON cc_invoice_history USING btree (idate);

CREATE INDEX ind_cc_phonelist_numbertodial ON cc_phonelist USING btree (numbertodial);

CREATE INDEX ind_cc_buy_dialprefix ON cc_buy_prefix USING btree (dialprefix);
CREATE INDEX ind_cc_sell_dialprefix ON cc_sell_prefix USING btree (dialprefix);


-- CREATE INDEX ind_cc_charge_id_cc_card ON cc_charge USING btree (card);
-- CREATE INDEX ind_cc_charge_id_cc_subscription_fee ON cc_charge USING btree (subscription_fee);
-- CREATE INDEX ind_cc_charge_creationdate  ON cc_charge USING btree (creationdate);
