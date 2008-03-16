-- Data for currencies

\unset ON_ERROR_STOP

COPY cc_currencies (id, currency, name, value,lastupdate) FROM STDIN WITH DELIMITER ';' ;
1;ALL;Albanian Lek;0.00974;\N
2;DZD;Algerian Dinar;0.01345;\N
3;XAL;Aluminium Ounces;1.08295;\N
4;ARS;Argentine Peso;0.32455;\N
5;AWG;Aruba Florin;0.55866;\N
6;AUD;Australian Dollar;0.73384;\N
7;BSD;Bahamian Dollar;1.00000;\N
8;BHD;Bahraini Dinar;2.65322;\N
9;BDT;Bangladesh Taka;0.01467;\N
10;BBD;Barbados Dollar;0.50000;\N
11;BYR;Belarus Ruble;0.00046;\N
12;BZD;Belize Dollar;0.50569;\N
13;BMD;Bermuda Dollar;1.00000;\N
14;BTN;Bhutan Ngultrum;0.02186;\N
15;BOB;Bolivian Boliviano;0.12500;\N
16;BRL;Brazilian Real;0.46030;\N
17;GBP;British Pound;1.73702;\N
18;BND;Brunei Dollar;0.61290;\N
19;BGN;Bulgarian Lev;0.60927;\N
20;BIF;Burundi Franc;0.00103;\N
21;KHR;Cambodia Riel;0.00000;\N
22;CAD;Canadian Dollar;0.86386;\N
23;KYD;Cayman Islands Dollar;1.16496;\N
24;XOF;CFA Franc (BCEAO);0.00182;\N
25;XAF;CFA Franc (BEAC);0.00182;\N
26;CLP;Chilean Peso;0.00187;\N
27;CNY;Chinese Yuan;0.12425;\N
28;COP;Colombian Peso;0.00044;\N
29;KMF;Comoros Franc;0.00242;\N
30;XCP;Copper Ounces;2.16403;\N
31;CRC;Costa Rica Colon;0.00199;\N
32;HRK;Croatian Kuna;0.16249;\N
33;CUP;Cuban Peso;1.00000;\N
34;CYP;Cyprus Pound;2.07426;\N
35;CZK;Czech Koruna;0.04133;\N
36;DKK;Danish Krone;0.15982;\N
37;DJF;Dijibouti Franc;0.00000;\N
38;DOP;Dominican Peso;0.03035;\N
39;XCD;East Caribbean Dollar;0.37037;\N
40;ECS;Ecuador Sucre;0.00004;\N
41;EGP;Egyptian Pound;0.17433;\N
42;SVC;El Salvador Colon;0.11426;\N
43;ERN;Eritrea Nakfa;0.00000;\N
44;EEK;Estonian Kroon;0.07615;\N
45;ETB;Ethiopian Birr;0.11456;\N
46;EUR;Euro;1.19175;\N
47;FKP;Falkland Islands Pound;0.00000;\N
48;GMD;Gambian Dalasi;0.03515;\N
49;GHC;Ghanian Cedi;0.00011;\N
50;GIP;Gibraltar Pound;0.00000;\N
51;XAU;Gold Ounces;555.55556;\N
52;GTQ;Guatemala Quetzal;0.13103;\N
53;GNF;Guinea Franc;0.00022;\N
54;HTG;Haiti Gourde;0.02387;\N
55;HNL;Honduras Lempira;0.05292;\N
56;HKD;Hong Kong Dollar;0.12884;\N
57;HUF;Hungarian Forint;0.00461;\N
58;ISK;Iceland Krona;0.01436;\N
59;INR;Indian Rupee;0.02253;\N
60;IDR;Indonesian Rupiah;0.00011;\N
61;IRR;Iran Rial;0.00011;\N
62;ILS;Israeli Shekel;0.21192;\N
63;JMD;Jamaican Dollar;0.01536;\N
64;JPY;Japanese Yen;0.00849;\N
65;JOD;Jordanian Dinar;1.41044;\N
66;KZT;Kazakhstan Tenge;0.00773;\N
67;KES;Kenyan Shilling;0.01392;\N
68;KRW;Korean Won;0.00102;\N
69;KWD;Kuwaiti Dinar;3.42349;\N
70;LAK;Lao Kip;0.00000;\N
71;LVL;Latvian Lat;1.71233;\N
72;LBP;Lebanese Pound;0.00067;\N
73;LSL;Lesotho Loti;0.15817;\N
74;LYD;Libyan Dinar;0.00000;\N
75;LTL;Lithuanian Lita;0.34510;\N
76;MOP;Macau Pataca;0.12509;\N
77;MKD;Macedonian Denar;0.01945;\N
78;MGF;Malagasy Franc;0.00011;\N
79;MWK;Malawi Kwacha;0.00752;\N
80;MYR;Malaysian Ringgit;0.26889;\N
81;MVR;Maldives Rufiyaa;0.07813;\N
82;MTL;Maltese Lira;2.77546;\N
83;MRO;Mauritania Ougulya;0.00369;\N
84;MUR;Mauritius Rupee;0.03258;\N
85;MXN;Mexican Peso;0.09320;\N
86;MDL;Moldovan Leu;0.07678;\N
87;MNT;Mongolian Tugrik;0.00084;\N
88;MAD;Moroccan Dirham;0.10897;\N
89;MZM;Mozambique Metical;0.00004;\N
90;NAD;Namibian Dollar;0.15817;\N
91;NPR;Nepalese Rupee;0.01408;\N
92;ANG;Neth Antilles Guilder;0.55866;\N
93;TRY;New Turkish Lira;0.73621;\N
94;NZD;New Zealand Dollar;0.65096;\N
95;NIO;Nicaragua Cordoba;0.05828;\N
96;NGN;Nigerian Naira;0.00777;\N
97;NOK;Norwegian Krone;0.14867;\N
98;OMR;Omani Rial;2.59740;\N
99;XPF;Pacific Franc;0.00999;\N
100;PKR;Pakistani Rupee;0.01667;\N
101;XPD;Palladium Ounces;277.77778;\N
102;PAB;Panama Balboa;1.00000;\N
103;PGK;Papua New Guinea Kina;0.33125;\N
104;PYG;Paraguayan Guarani;0.00017;\N
105;PEN;Peruvian Nuevo Sol;0.29999;\N
106;PHP;Philippine Peso;0.01945;\N
107;XPT;Platinum Ounces;1000.00000;\N
108;PLN;Polish Zloty;0.30574;\N
109;QAR;Qatar Rial;0.27476;\N
110;ROL;Romanian Leu;0.00000;\N
111;RON;Romanian New Leu;0.34074;\N
112;RUB;Russian Rouble;0.03563;\N
113;RWF;Rwanda Franc;0.00185;\N
114;WST;Samoa Tala;0.35492;\N
115;STD;Sao Tome Dobra;0.00000;\N
116;SAR;Saudi Arabian Riyal;0.26665;\N
117;SCR;Seychelles Rupee;0.18114;\N
118;SLL;Sierra Leone Leone;0.00034;\N
119;XAG;Silver Ounces;9.77517;\N
120;SGD;Singapore Dollar;0.61290;\N
121;SKK;Slovak Koruna;0.03157;\N
122;SIT;Slovenian Tolar;0.00498;\N
123;SOS;Somali Shilling;0.00000;\N
124;ZAR;South African Rand;0.15835;\N
125;LKR;Sri Lanka Rupee;0.00974;\N
126;SHP;St Helena Pound;0.00000;\N
127;SDD;Sudanese Dinar;0.00427;\N
128;SRG;Surinam Guilder;0.36496;\N
129;SZL;Swaziland Lilageni;0.15817;\N
130;SEK;Swedish Krona;0.12609;\N
131;CHF;Swiss Franc;0.76435;\N
132;SYP;Syrian Pound;0.00000;\N
133;TWD;Taiwan Dollar;0.03075;\N
134;TZS;Tanzanian Shilling;0.00083;\N
135;THB;Thai Baht;0.02546;\N
136;TOP;Tonga Paanga;0.48244;\N
137;TTD;Trinidad&Tobago Dollar;0.15863;\N
138;TND;Tunisian Dinar;0.73470;\N
139;USD;U.S. Dollar;1.00000;\N
140;AED;UAE Dirham;0.27228;\N
141;UGX;Ugandan Shilling;0.00055;\N
142;UAH;Ukraine Hryvnia;0.19755;\N
143;UYU;Uruguayan New Peso;0.04119;\N
144;VUV;Vanuatu Vatu;0.00870;\N
145;VEB;Venezuelan Bolivar;0.00037;\N
146;VND;Vietnam Dong;0.00006;\N
147;YER;Yemen Riyal;0.00510;\N
148;ZMK;Zambian Kwacha;0.00031;\N
149;ZWD;Zimbabwe Dollar;0.00001;\N
150;GYD;Guyana Dollar;0.00527;\N
\.

SET CLIENT_ENCODING TO UTF8;
UPDATE cc_currencies SET csign = '$', sign_pre=true WHERE currency = 'USD';
UPDATE cc_currencies SET csign = '€', sign_pre=true WHERE currency = 'EUR';
UPDATE cc_currencies SET csign = '£', sign_pre=true WHERE currency = 'GBP';
UPDATE cc_currencies SET csign = '¥', sign_pre=true WHERE currency = 'JPY';

\echo Currencies copied in.
