--- Functions

\echo Dropping functions

DROP FUNCTION IF EXISTS agent_charge_std (charge character varying, ssession bigint, descr text);
DROP FUNCTION IF EXISTS agent_create_all_invoices (s_agentid bigint, s_intv interval);
DROP FUNCTION IF EXISTS agent_create_invoice (s_agentid bigint, s_startdate timestamp without time zone, s_stopdate timestamp without time zone);
DROP FUNCTION IF EXISTS agent_gen_regular (p_agent_id integer, p_def boolean, p_prepay integer, p_regpat text, p_alias_start integer, p_lenuname integer, p_climit integer, p_eexpire integer, p_eaeday timestamp without time zone, p_edays integer);
DROP FUNCTION IF EXISTS agent_manual_commission (s_inv_id bigint);
DROP FUNCTION IF EXISTS agent_pay_invoice (s_inv_id bigint, s_amount numeric);
DROP FUNCTION IF EXISTS booth_start (booth bigint, agent_id bigint);
DROP FUNCTION IF EXISTS booth_stop (booth bigint, agent_id bigint);
DROP FUNCTION IF EXISTS carry_session (sid bigint, agentid_p bigint);
DROP FUNCTION IF EXISTS cc_agent_refill_it () CASCADE;
DROP FUNCTION IF EXISTS cc_agentpay_it () CASCADE;
DROP FUNCTION IF EXISTS cc_agentpay_itd () CASCADE;
DROP FUNCTION IF EXISTS cc_agentpay_itu () CASCADE;
DROP FUNCTION IF EXISTS cc_booth_no_agent_update () CASCADE;
DROP FUNCTION IF EXISTS cc_booth_remove_def_card () CASCADE;
DROP FUNCTION IF EXISTS cc_booth_set_card () CASCADE;
DROP FUNCTION IF EXISTS cc_booth_upd_callerid () CASCADE;
DROP FUNCTION IF EXISTS cc_calc_daysleft (agentid bigint, curtime timestamp with time zone, backi interval, OUT credit numeric, OUT climit numeric, OUT avg_time interval, OUT avg_charges numeric, OUT days_left numeric);
DROP FUNCTION IF EXISTS cc_charge_it () CASCADE;
DROP FUNCTION IF EXISTS cc_charge_itd () CASCADE;
DROP FUNCTION IF EXISTS cc_charge_itu () CASCADE;
DROP FUNCTION IF EXISTS cc_invoice_lock_f () CASCADE;
DROP FUNCTION IF EXISTS conv_currency (money_sum numeric, from_cur character, to_cur character);
DROP FUNCTION IF EXISTS divide_time (div1 interval, div2 interval);
DROP FUNCTION IF EXISTS fmt_date (date timestamp without time zone);
DROP FUNCTION IF EXISTS fmt_mins (seconds integer);
DROP FUNCTION IF EXISTS format_currency (money_sum double precision, from_cur character, to_cur character);
DROP FUNCTION IF EXISTS format_currency (money_sum numeric, from_cur character, to_cur character);
DROP FUNCTION IF EXISTS format_currency2 (money_sum numeric, from_cur character, to_cur character);
DROP FUNCTION IF EXISTS gettext (ptxt text, plang character varying) CASCADE;
DROP FUNCTION IF EXISTS gettext_add_missing (lang character varying) CASCADE;
DROP FUNCTION IF EXISTS gettext_r (ptxt text) CASCADE;
DROP FUNCTION IF EXISTS gettext_ri (ptxt text) CASCADE;
DROP FUNCTION IF EXISTS gettexti (pid integer, plang character varying) CASCADE;
DROP FUNCTION IF EXISTS lchop (str character varying, n integer) CASCADE;
DROP FUNCTION IF EXISTS mknumpasswd (len integer) CASCADE;
DROP FUNCTION IF EXISTS mkpasswd (len integer) CASCADE;
DROP FUNCTION IF EXISTS pay_session (sid bigint, agentid_p bigint, do_close boolean, do_carry boolean);
DROP FUNCTION IF EXISTS rateengine (id_card bigint, dialstring text);
DROP FUNCTION IF EXISTS rateengine2 (tgid bigint, dialstring text);
DROP FUNCTION IF EXISTS simulate_calls ();
DROP FUNCTION IF EXISTS tel_expand_prefix (pr character varying) CASCADE;
DROP FUNCTION IF EXISTS text_array_append50 (arr text[], str text) CASCADE;
DROP FUNCTION IF EXISTS text_array_comma (text[]) CASCADE;
