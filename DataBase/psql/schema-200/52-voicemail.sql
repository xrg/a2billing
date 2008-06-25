-- Views for voicemail

-- Since most of the data is already in cc_card, realtime voicemail
-- need only a view.


CREATE OR REPLACE VIEW realtime_voicemail AS
	SELECT id AS uniqueid, id AS customer_id,
		'default'::TEXT AS context,
		username AS mailbox,
		vm_password AS password,
		( COALESCE(substr(cc_card.firstname,1,1)||'. ', '') || cc_card.lastname) AS fullname, -- TODO
		email, ''::TEXT AS pager,
		vm_tstamp AS "timestamp"
	    FROM cc_card
	    WHERE id IN ( SELECT cs.card
	    		FROM card_subscription AS cs, subscription_feature_templ AS sft 
	    		WHERE cs.template = sft.id AND sft.feature = 'voicemail'
			  AND cs.status=1 AND cs.activedate <= now() 
			  AND ( cs.expiredate IS NULL OR cs.expiredate > now()) )
	;
	
GRANT ALL ON realtime_voicemail TO a2b_group;