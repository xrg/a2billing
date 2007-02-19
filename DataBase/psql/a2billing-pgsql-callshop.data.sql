-- Additional data for the callshop database
SET client_encoding TO 'UTF8';
-- be careful with your editors, this *is* an UTF-8 text file!

INSERT INTO cc_templatemail VALUES ('forgetagentpw', 'info@call-labs.com', 'Call-Labs', 'Login Information', 'Your login information is as below:

Your account is $login

Your password is $password

http://call-labs.com/A2BAgent_UI/


Kind regards,
Call Labs
', '');

UPDATE cc_currencies SET sign_pre = true, csign = '€' WHERE currency = 'EUR';
UPDATE cc_currencies SET sign_pre = true, csign = '$' WHERE currency = 'USD';
UPDATE cc_currencies SET sign_pre = true, csign = '£' WHERE currency = 'GBP';