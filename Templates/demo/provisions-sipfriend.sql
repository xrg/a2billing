--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

--
-- Name: provision_group_id_seq; Type: SEQUENCE SET; Schema: public; Owner: a2billing
--

SELECT pg_catalog.setval('provision_group_id_seq', 1, true);


--
-- Name: provisions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: a2billing
--

SELECT pg_catalog.setval('provisions_id_seq', 8, true);


--
-- Data for Name: provision_group; Type: TABLE DATA; Schema: public; Owner: a2billing
--

COPY provision_group (id, categ, model, name, sub_name, args, options, metric) FROM stdin;
1	sip-peer	ast-ini-card	Name	%username	\N	0	10
\.


--
-- Data for Name: provisions; Type: TABLE DATA; Schema: public; Owner: a2billing
--

COPY provisions (id, grp_id, name, sub_name, valuef, options, metric) FROM stdin;
1	1	type		friend	0	10
2	1	username		%username	0	10
3	1	secret		%userpass	0	10
4	1	host		sip.xrg.awmn	0	10
5	1	nat		no	0	10
6	1	canreinvite		nonat	0	10
7	1	disallow		all	0	11
8	1	allow		speex,ilbc,gsm,alaw	0	12
\.


--
-- PostgreSQL database dump complete
--

