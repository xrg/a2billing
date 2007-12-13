-- All the functionality of realtime users/peers is done through
-- views, that combine several tables.

-- The use of views actually helps prevent * from updating the wrong
-- fields.

CREATE OR REPLACE VIEW cc_sip_peers AS
SELECT 
	
	FROM cc_ast_users AS cau
	;
	
	
-- LEFT OUTER JOIN cc_ast_instance ON cau.id = cc_ast_instance.userid


--- Now, define update rules for the views. That way, asterisk will be
-- able to update *only some* of the information, such as where the user
-- is actually registered.

