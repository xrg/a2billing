--
-- A2Billing database - update database schema - v1.2.3 to update to v1.2.4
--

/* 

To create the database : 

mysql -u root -p"root password" < UPDATE-a2billing-v1.2.3-to-v1.2.4-mysql.sql

*/


ALTER TABLE cc_tariffplan ADD COLUMN calleridprefix CHAR(30) NOT NULL DEFAULT 'all';


INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (150, 'GYD', 'Guyana Dollar (GYD)', 0.00527,  'USD');



ALTER TABLE cc_charge ADD COLUMN id_cc_did bigint ;
ALTER TABLE cc_charge ALTER COLUMN id_cc_did SET DEFAULT 0;

CREATE TABLE cc_did_use (
    id BIGINT NOT NULL AUTO_INCREMENT,
    id_cc_card BIGINT,
    id_did BIGINT NOT NULL,
    reservationdate TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    releasedate TIMESTAMP,
    activated INT DEFAULT 0,
    month_payed INT DEFAULT 0,
    PRIMARY KEY (id)
);





-- cc_prefix Table	

CREATE TABLE cc_prefix (
	id BIGINT NOT NULL AUTO_INCREMENT,
	prefixe VARCHAR(50) NOT NULL,
	destination VARCHAR(100) NOT NULL,
	PRIMARY KEY (id)
);

INSERT INTO cc_prefix (destination,prefixe) VALUES ('Afghanistan','93');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Albania','355');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Algeria','213');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('American Samoa','684');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Andorra','376');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Angola','244');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Anguilla','1-264');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Antarctica','672');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Antigua','1-268');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Argentina','54');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Armenia','374');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Aruba','297');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Ascension','247');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Australia','61');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Australian External Territories','672');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Austria','43');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Azerbaijan','994');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Bahamas','1-242');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Bahrain','973');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Bangladesh','880');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Barbados','1-246');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Barbuda','1-268');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Belarus','375');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Belgium','32');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Belize','501');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Benin','229');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Bermuda','1-441');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Bhutan','975');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Bolivia','591');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Bosnia & Herzegovina','387');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Botswana','267');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Brazil','55');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Brasil Telecom','5514');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Brazil Telefonica','5515');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Brazil Embratel','5521');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Brazil Intelig','5523');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Brazil Telemar','5531');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Brazil mobile phones','550');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('British Virgin Islands','1-284');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Brunei Darussalam','673');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Bulgaria','359');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Burkina Faso','226');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Burundi','257');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Cambodia','855');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Cameroon','237');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Canada','1');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Cape Verde Islands','238');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Cayman Islands','1-345');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Central African Republic','236');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Chad','235');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Chatham Island (New Zealand)','64');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Chile','56');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('China (PRC)','86');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Christmas Island','61-8');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Cocos-Keeling Islands','61');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Colombia','57');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Colombia Mobile Phones','573');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Colombia Orbitel','575');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Colombia ETB','577');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Colombia Telecom','579');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Comoros','269');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Congo','242');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Congo, Dem. Rep. of  (former Zaire)','243');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Cook Islands','682');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Costa Rica','506');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Côte d''Ivoire (Ivory Coast)','225');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Croatia','385');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Cuba','53');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Cuba (Guantanamo Bay)','5399');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Curaçao','599');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Cyprus','357');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Czech Republic','420');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Denmark','45');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Diego Garcia','246');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Djibouti','253');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Dominica','1-767');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Dominican Republic','1-809');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('East Timor','670');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Easter Island','56');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Ecuador','593');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Egypt','20');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('El Salvador','503');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Ellipso (Mobile Satellite service)','8812');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('EMSAT (Mobile Satellite service)','88213');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Equatorial Guinea','240');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Eritrea','291');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Estonia','372');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Ethiopia','251');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Falkland Islands (Malvinas)','500');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Faroe Islands','298');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Fiji Islands','679');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Finland','358');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('France','33');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('French Antilles','596');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('French Guiana','594');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('French Polynesia','689');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Gabonese Republic','241');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Gambia','220');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Georgia','995');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Germany','49');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Ghana','233');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Gibraltar','350');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Global Mobile Satellite System (GMSS)','881');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('ICO Global','8810-8811');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Ellipso','8812-8813');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Iridium','8816-8817');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Globalstar','8818-8819');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Globalstar (Mobile Satellite Service)','8818-8819');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Greece','30');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Greenland','299');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Grenada','1-473');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Guadeloupe','590');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Guam','1-671');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Guantanamo Bay','5399');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Guatemala','502');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Guinea-Bissau','245');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Guinea','224');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Guyana','592');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Haiti','509');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Honduras','504');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Hong Kong','852');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Hungary','36');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('ICO Global (Mobile Satellite Service)','8810-8811');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Iceland','354');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('India','91');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Indonesia','62');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Inmarsat (Atlantic Ocean - East)','871');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Inmarsat (Atlantic Ocean - West)','874');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Inmarsat (Indian Ocean)','873');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Inmarsat (Pacific Ocean)','872');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Inmarsat SNAC','870');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('International Freephone Service','800');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('International Shared Cost Service (ISCS)','808');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Iran','98');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Iraq','964');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Ireland','353');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Iridium (Mobile Satellite service)','8816-8817');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Israel','972');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Italy','39');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Jamaica','1-876');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Japan','81');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Jordan','962');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Kazakhstan','7');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Kenya','254');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Kiribati','686');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Korea (North)','850');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Korea (South)','82');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Kuwait','965');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Kyrgyz Republic','996');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Laos','856');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Latvia','371');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Lebanon','961');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Lesotho','266');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Liberia','231');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Libya','218');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Liechtenstein','423');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Lithuania','370');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Luxembourg','352');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Macao','853');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Macedonia (Former Yugoslav Rep of.)','389');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Madagascar','261');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Malawi','265');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Malaysia','60');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Maldives','960');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Mali Republic','223');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Malta','356');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Marshall Islands','692');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Martinique','596');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Mauritania','222');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Mauritius','230');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Mayotte Island','269');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Mexico','52');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Micronesia, (Federal States of)','691');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Midway Island','1-808');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Moldova','373');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Monaco','377');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Mongolia','976');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Montserrat','1-664');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Morocco','212');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Mozambique','258');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Myanmar','95');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Namibia','264');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Nauru','674');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Nepal','977');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Netherlands','31');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Netherlands Antilles','599');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Nevis','1-869');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('New Caledonia','687');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('New Zealand','64');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Nicaragua','505');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Niger','227');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Nigeria','234');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Niue','683');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Norfolk Island','672');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Northern Marianas Islands(Saipan, Rota, & Tinian)','1-670');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Norway','47');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Oman','968');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Pakistan','92');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Palau','680');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Palestinian Settlements','970');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Panama','507');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Papua New Guinea','675');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Paraguay','595');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Peru','51');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Philippines','63');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Poland','48');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Portugal','351');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Puerto Rico','1-787');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Qatar','974');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Réunion Island','262');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Romania','40');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Russia','7');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Rwandese Republic','250');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('St. Helena','290');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('St. Kitts/Nevis','1-869');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('St. Lucia','1-758');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('St. Pierre & Miquelon','508');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('St. Vincent & Grenadines','1-784');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('San Marino','378');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('São Tomé and Principe','239');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Saudi Arabia','966');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Senegal','221');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Serbia and Montenegro','381');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Seychelles Republic','248');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Sierra Leone','232');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Singapore','65');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Slovak Republic','421');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Slovenia','386');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Solomon Islands','677');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Somali Democratic Republic','252');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('South Africa','27');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Spain','34');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Sri Lanka','94');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Sudan','249');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Suriname','597');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Swaziland','268');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Sweden','46');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Switzerland','41');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Syria','963');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Taiwan','886');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Tajikistan','992');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Tanzania','255');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Thailand','66');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Thuraya (Mobile Satellite service)','88216');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Togolese Republic','228');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Tokelau','690');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Tonga Islands','676');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Trinidad & Tobago','1-868');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Tunisia','216');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Turkey','90');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Turkmenistan','993');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Turks and Caicos Islands','1-649');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Tuvalu','688');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Uganda','256');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Ukraine','380');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('United Arab Emirates','971');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('United Kingdom','44');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('United States of America','1');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('US Virgin Islands','1-340');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Universal Personal Telecommunications (UPT)','878');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Uruguay','598');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Uzbekistan','998');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Vanuatu','678');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Vatican City','39');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Venezuela','58');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Venezuela Etelix','58102');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Venezuela http://www.multiphone.net.ve','58107');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Venezuela CANTV','58110');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Venezuela Convergence Comunications','58111');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Venezuela Telcel, C.A.','58114');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Venezuela Totalcom Venezuela','58119');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Venezuela Orbitel de Venezuela, C.A. ENTEL Venezuela','58123');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Venezuela LD Telecomunicaciones, C.A.','58150');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Venezuela Telecomunicaciones NGTV','58133');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Venezuela Veninfotel Comunicaciones','58199');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Vietnam','84');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Wake Island','808');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Wallis and Futuna Islands','681');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Western Samoa','685');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Yemen','967');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Zambia','260');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Zanzibar','255');
INSERT INTO cc_prefix (destination,prefixe) VALUES ('Zimbabwe','263');
