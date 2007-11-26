

ALTER TABLE cc_tariffgroup RENAME id_cc_package_offer TO package_offer;
ALTER TABLE cc_tariffgroup_plan RENAME idtariffgroup TO tgid;
ALTER TABLE cc_tariffgroup_plan RENAME idtariffplan TO tpid;
