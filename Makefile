# This mainly takes care of produced code/files, like the gettext ones.

UIS= A2BAgent_UI A2Billing_UI A2BCustomer_UI
LANGS-A2BAgent_UI=el_GR en_US es_ES fr_FR it_IT pl_PL pt_PT
LANGS-A2Billing_UI=en_US pt_BR el_GR
LANGS-A2BCustomer_UI=en_US el_GR es_ES fr_FR it_IT pl_PL pt_PT pt_BR ro_RO ru_RU tr_TR ur_PK zh_TW

all: pofiles binaries

test:
	@echo $(UIS:%=%/lib/locale/messages.pot)

messages: $(UIS:%=%/lib/locale/messages.pot)

%/lib/locale/messages.pot: % FORCE
	@xgettext --omit-header -o $@ -L PHP $$(find $< -name '*.php') $$(find $< -name '*.inc')

define UI_template
$(1)/lib/locale/$(2)/LC_MESSAGES/messages.po: $(1)/lib/locale/messages.pot
	msgmerge --backup=numbered -U $$@ $$<

$(1)/lib/locale/$(2)/LC_MESSAGES/messages.mo: $(1)/lib/locale/$(2)/LC_MESSAGES/messages.po
	msgfmt -o $$@ $$<
	
pofiles: $(1)/lib/locale/$(2)/LC_MESSAGES/messages.po
binaries: $(1)/lib/locale/$(2)/LC_MESSAGES/messages.mo
endef

$(foreach uii,$(UIS),$(foreach lang,$(LANGS-$(uii)),$(eval $(call UI_template,$(uii),$(lang)))))

gettexts:
	@echo "Gettext compilation finished, you can transfer them to your web server now."
	@echo
	@echo "Please note that you may need to *restart* the httpd to let new texts appear"


FORCE: ;
.SILENT: messages test %/lib/locale/messages.pot
#eof
