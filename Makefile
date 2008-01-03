# This mainly takes care of produced code/files, like the gettext ones.

DST_DOMAINS=admin agent customer signup
SRC_DOMAINS=common $(DST_DOMAINS)

UIS= A2BAgent_UI A2Billing_UI A2BCustomer_UI
LANGS-agent=el_GR en_US es_ES fr_FR it_IT pl_PL pt_PT
LANGS-admin=en_US pt_BR el_GR
LANGS-customer=en_US el_GR es_ES fr_FR it_IT pl_PL pt_PT pt_BR ro_RO ru_RU tr_TR ur_PK zh_TW
LANGS-common=en_US el_GR es_ES fr_FR it_IT pl_PL pt_PT pt_BR ro_RO ru_RU tr_TR ur_PK zh_TW

CODE-admin=A2Billing_UI
CODE-agent=A2BAgent_UI
CODE-customer=A2BCustomer_UI
CODE-signup=Signup
CODE-common=common

all: pofiles binaries

test:
	@echo Src domains: $(SRC_DOMAINS:%=common/lib/locale/%.pot)

messages: $(SRC_DOMAINS:%=common/lib/locale/%.pot)

define DOMAIN_template
common/lib/locale/$(1).files: FORCE
	@find $$(CODE-$(1)) -name '*.php' > $$@.tmp
	@find $$(CODE-$(1)) -name '*.inc' >> $$@.tmp
	@if [ -f $$@ ] && diff -q $$@ $$@.tmp > /dev/null ; then \
		rm -f $$@.tmp ; \
		else mv -f $$@.tmp $$@ ; \
		fi

common/lib/locale/$(1).pot: common/lib/locale/$(1).files
	@[ -d common/lib/locale/ ] || mkdir -p common/lib/locale/
	@xgettext --omit-header -o $$@ -L PHP -f common/lib/locale/$(1).files
endef

define COMMON_template
common/lib/locale/$(1)/LC_MESSAGES/common.po: common/lib/locale/common.pot
	msgmerge --backup=numbered -U $$@ $$<
endef

define UI_template
common/lib/locale/$(2)/LC_MESSAGES/$(1).po: common/lib/locale/$(1).pot
	msgmerge --backup=numbered -U $$@ $$<

$(CODE-$(1))/lib/locale/$(2)/LC_MESSAGES/$(1).mo: common/lib/locale/$(2)/LC_MESSAGES/$(1).po common/lib/locale/$(2)/LC_MESSAGES/common.po
	@if [ ! -d $(CODE-$(1))/lib/locale/$(2)/LC_MESSAGES/ ] ; then mkdir -p $(CODE-$(1))/lib/locale/$(2)/LC_MESSAGES/ ; fi
	msgcat --use-first $$^ | msgfmt -o $$@ '-'
	
pofiles: common/lib/locale/$(2)/LC_MESSAGES/$(1).po common/lib/locale/$(2)/LC_MESSAGES/common.po
binaries: common/lib/locale/$(2)/LC_MESSAGES/$(1).mo
endef

$(foreach clang,$(LANGS-common),$(eval $(call COMMON_template,$(clang))))
$(foreach uii,$(SRC_DOMAINS),$(eval $(call DOMAIN_template,$(uii))))
$(foreach uii,$(DST_DOMAINS),$(foreach lang,$(LANGS-$(uii)),$(eval $(call UI_template,$(uii),$(lang)))))

gettexts:
	@echo "Gettext compilation finished, you can transfer them to your web server now."
	@echo
	@echo "Please note that you may need to *restart* the httpd to let new texts appear"


FORCE: ;
.SILENT: messages test common/lib/locale/%.pot
#eof
