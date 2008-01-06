-- Standard texts. Without them, some functions may not operate

INSERT INTO cc_paytypes(id,side,preset) VALUES(gettext_ri('Carry to next session'),5,'carry');
INSERT INTO cc_paytypes(id,side,preset) VALUES(gettext_ri('Carried from previous session'),5,'carried');
INSERT INTO cc_paytypes(id,side,preset) VALUES(gettext_ri('Pay the account'),5,'settle');
INSERT INTO cc_paytypes(id,side,preset) VALUES(gettext_ri('Pay back to customer'),5,'pay-back');
INSERT INTO cc_paytypes(id,side,preset) VALUES(gettext_ri('Prepay money into card'),5,'prepay');

INSERT INTO cc_paytypes(id,side,preset) VALUES(gettext_ri('Manual commission credit'),1,'manual-commission');
INSERT INTO cc_paytypes(id,side,preset) VALUES(gettext_ri('Auto commission credit'),1,'auto-commission');
INSERT INTO cc_paytypes(id,side,preset) VALUES(gettext_ri('Payment from agent'),2,'agent-pay');
