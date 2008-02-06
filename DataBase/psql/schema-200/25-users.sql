--- Tables for asterisk users
--- We don't make any distinction between SIP/IAX users, for a start


-- ;hash323 = yes
-- callwaiting = yes
-- threewaycalling = yes
-- callwaitingcallerid = yes
-- transfer = yes
-- canpark = yes
-- cancallforward = yes
-- callreturn = yes
-- callgroup = 1
-- pickupgroup = 1

CREATE TABLE cc_ast_users_config(
    id SERIAL PRIMARY KEY,
    cfg_name  TEXT NOT NULL UNIQUE,
    -- General:
    "type" VARCHAR(6) DEFAULT 'friend'::VARCHAR NOT NULL,
    "context" VARCHAR(80),
    trunk VARCHAR(3) DEFAULT 'no' NOT NULL,
    videosupport VARCHAR(3),
    fromdomain VARCHAR(80),
    amaflags VARCHAR(7) DEFAULT 'billing',
    dtmfmode VARCHAR(7),
    progressinband VARCHAR(5),
    incominglimit INTEGER,
    outgoinglimit INTEGER,
    --NAT:
    nat VARCHAR(6) , -- May be null!
    canreinvite VARCHAR(6) DEFAULT 'nonat'::VARCHAR,
    insecure VARCHAR(4),
    --RTP:
    rtpkeepalive SMALLINT,
    rtpholdtimeout SMALLINT,
    rtptimeout SMALLINT,
    qualify VARCHAR(7),
    -- IAX2:
    iax_xfer VARCHAR(10),
    iax_auth VARCHAR(10) NOT NULL DEFAULT 'md5',
    jitterbuffer VARCHAR(7),
       -- TODO: codecpriority, sendani, peercontext, /sourceaddress/, adsi
    -- Connection
    defport INTEGER,
    permit VARCHAR(95),
    deny VARCHAR(95),
    mask VARCHAR(95),
    -- Codecs:
    allow VARCHAR(100),
    disallow VARCHAR(100),
    -- Other:
    cancallforward VARCHAR(3),
    musiconhold VARCHAR(100),
    setvar VARCHAR(100)
);

CREATE TABLE cc_ast_users (
    id BIGSERIAL PRIMARY KEY,
    card_id BIGINT REFERENCES cc_card(id),
    booth_id INTEGER REFERENCES cc_booth(id),
    config INTEGER REFERENCES cc_ast_users_config(id) NOT NULL,
    has_sip BOOLEAN DEFAULT true NOT NULL,
    has_iax BOOLEAN DEFAULT true NOT NULL,
    defaultip INET,
    fromuser VARCHAR(80),
    host VARCHAR(31) DEFAULT 'dynamic' NOT NULL,
    peernameb TEXT,
    secretb   TEXT,
    callerid  TEXT,
    CHECK( card_id IS NOT NULL OR booth_id IS NOT NULL)
);

/* FYI asterisk issues an update like:
	UPDATE <sippeers>
		SET ipaddr = ... , port = ... , regseconds= ...,
		username = ... [, fullcontact= ...] [, regserver = ... ]
	WHERE name = <peername>;
   and, with iax:
	UPDATE <iaxpeers>
		SET ipaddr = ... , port = ... , regseconds= ...,
	WHERE name = <peername>;
   
*/

/** Create a separate table for user registrations. That table will
    only contain one server and thus allow a user to register to
    multiple servers at once!
    
    There can be both static and dynamic entries: static are for
    non-realtime registrations, such as remote, statically cfged 
    servers.
*/

CREATE TABLE cc_ast_instance (
    userid BIGINT REFERENCES cc_ast_users(id) NOT NULL,
    srvid  INTEGER REFERENCES cc_a2b_server(id) NOT NULL,
    dyn    BOOLEAN DEFAULT true NOT NULL,
    sipiax integer not null default 0,
    -- Fields asterisk sends:
    ipaddr      VARCHAR(16),
    port        INTEGER,
    regseconds  INTEGER,
    username    VARCHAR(40),
    fullcontact VARCHAR(80),
    regserver   VARCHAR(40),
    useragent   TEXT,
    PRIMARY KEY(userid,srvid,sipiax)
);


--     accountcode VARCHAR(20),    -- Card username!
--     callerid VARCHAR(80),	-- Fwd the clid that will be picked by the algo...
--     callgroup VARCHAR(10),
--     ipaddr VARCHAR(15) DEFAULT ''::VARCHAR NOT NULL,
--     "language" VARCHAR(2),
--     mailbox VARCHAR(50),
--     md5secret VARCHAR(80),
--     name VARCHAR(80) DEFAULT ''::VARCHAR NOT NULL,
--     pickupgroup VARCHAR(10),
--     regexten VARCHAR(20),
--     regseconds integer DEFAULT 0 NOT NULL,
--     restrictcid VARCHAR(1),
--     secret VARCHAR(80),
--     username VARCHAR(80) DEFAULT ''::VARCHAR NOT NULL,	
