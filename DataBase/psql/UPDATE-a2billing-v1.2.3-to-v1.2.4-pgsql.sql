ALTER TABLE cc_charge ADD COLUMN id_cc_did bigint ;
ALTER TABLE cc_charge ALTER COLUMN id_cc_did SET DEFAULT 0;

create table cc_did_use (
id serial not null ,
id_cc_card bigint,
id_did bigint not null,
reservationdate timestamp not null default now(),
releasedate timestamp,
activated integer default 0,
month_payed integer default 0
);

