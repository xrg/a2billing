# This mainly takes care of produced code/files, like the gettext ones.

UIS= A2BAgent_UI A2Billing_UI A2BCustomer_UI
LANGS-A2BAgent_UI=el_GR
LANGS-A2Billing_UI=en_US
LANGS-A2BCustomer_UI=en_US

all: gettexts

test:
	@echo $(UIS:%=%/lib/locale/messages.pot)

messages: $(UIS:%=%/lib/locale/messages.pot)


%/lib/locale/messages.pot: % FORCE
	@xgettext -o $@ -L PHP $$(find $< -name '*.php') $$(find $< -name '*.inc')
	@for LANG in $(LANGS-$<) ; do \
		LDIR=$</lib/locale/$$LANG/LC_MESSAGES ; \
		echo "Merging into $$LANG locales for $<" ;\
		msgmerge -U $$LDIR/messages.po $@ ; \
	done

gettexts:
	@echo "Gettext compilation finished, you can transfer them to your web server now."
	@echo
	@echo "Please note that you may need to *restart* the httpd to let new texts appear"


FORCE: ;
.SILENT: messages test %/lib/locale/messages.pot
#eof
