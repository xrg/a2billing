# Asterisk 2 Billing software
%define git_repodir /home/panos/Δουλειά/MinMax/a2b/
%define git_repo asterisk2billing
%define git_head v200-rpm

%define name a2billing
%define version 2.0.0
%define release pre1

Name:		%{name}
Version:	%{version}
Release:	%{release}
Summary:	Asterisk 2 Billing platform
Group:		System/Servers
BuildArch:	noarch
Prefix:		%{_datadir}
License:	GPL
Source0:	a2billing-%{version}.tar.gz
URL: 		http://www.asterisk2billing.org

BuildRequires:	gettext
Requires(pre): rpm-helper
Requires(postun): rpm-helper
Requires(post): rpm-helper
Requires(preun): rpm-helper

Requires:	%{name}-config
Requires:	%{name}-admin == %{version}-%{release}
Requires:	%{name}-customer == %{version}-%{release}
Requires:	%{name}-AGI == %{version}-%{release}

Requires:	postgresql >= 8.2.5
Requires:	php-pgsql
Requires:	php-gettext


%description
Asterisk2Billing is a frontend to the asterisk PBX,
raising it to a full telephony + billing platform.

This is a metapackage that contains all necessary elements
to run a2billing on a single server.

%package config
Summary:	Common configuration file for A2Billing
Group:		System/Servers

%description config
This package contains the configuration file, common to
all other a2billing sub-packages. Install this one and
modify it to suit your needs.


%package admin
Summary:	Administrator web interface
Group:		System/Servers
Requires:	%{name}-config
Requires:	apache-base >= 2.2.4
Requires:	apache-mod_ssl
Requires:	apache-mod_php >= 5.2.1

%description admin
The administrator web-interface to a2billing.

%post admin
%_post_webapp

%postun admin
%_postun_webapp

%package customer
Summary:	Customer web interface
Group:		System/Servers
Requires:	%{name}-config
Requires:	apache-base >= 2.2.4
Requires:	apache-mod_ssl
Requires:	apache-mod_php >= 5.2.1

%description customer
The web-interface for retail customers

%post customer
%_post_webapp

%postun customer
%_postun_webapp

%package agent
Summary:	Agent web interface
Group:		System/Servers
Requires:	%{name}-config
Requires:	apache-base >= 2.2.4
Requires:	apache-mod_ssl
Requires:	apache-mod_php >= 5.2.1

%post agent
%_post_webapp

%postun agent
%_postun_webapp

%description agent
Callshop (agent) web-interface.


%package signup
Summary:	Signup web interface
Group:		System/Servers
Requires:	%{name}-config
Requires:	apache-base >= 2.2.4
Requires:	apache-mod_ssl
Requires:	apache-mod_php >= 5.2.1

%post signup
%_post_webapp

%postun signup
%_postun_webapp

%description signup
Web signup pages for Asterisk2Billing.

%package AGI
Summary:	Asterisk interface
Group:		System/Servers
Requires:	%{name}-config
Requires:	asterisk >= 1.4.19
Requires:	php-pcntl

%description AGI
This package provides the necessary files for an asterisk server.

%package dbadmin
Summary:	Database files and scripts
Group:		System/Servers
# Requires:	%{name}-config
Requires:	cron-daemon

%description dbadmin
Install this package into some machine that is client to the
database. Then, the database for %{name} can be built from that
host.
Additionally, maintenance tasks can be performed from that host.

%post dbadmin
%{_datadir}/a2billing/Database/build_database.sh

%postun dbadmin
#TODO: backup the database here..

%prep
%git_get_source
%setup -q

%build
# just make the translations and the css
%make

%install
[ -n "%{buildroot}" -a "%{buildroot}" != / ] && rm -rf %{buildroot}

#remove some libs that shouldn't go to a production system
rm -rf common/lib/adodb/tests
rm -rf common/lib/adodb/pear
rm -f common/lib/adodb/adodb-pear.inc.php
rm -f common/lib/adodb/adodb-errorpear.inc.php
rm -rf common/lib/adodb/contrib

install -D a2billing.conf %{buildroot}%{_sysconfdir}/a2billing.conf
install -d %{buildroot}%{_datadir}/a2billing
install -d %{buildroot}%{_datadir}/a2billing/admin
install -d %{buildroot}%{_datadir}/a2billing/customer
install -d %{buildroot}%{_datadir}/a2billing/agent
install -d %{buildroot}%{_datadir}/a2billing/signup
install -d %{buildroot}%{_datadir}/a2billing/Database

install LICENSE FEATURES_LIST %{buildroot}%{_datadir}/a2billing
cp -LR  A2Billing_UI/* %{buildroot}%{_datadir}/a2billing/admin
cp -LR  A2BCustomer_UI/* %{buildroot}%{_datadir}/a2billing/customer
cp -LR  A2BAgent_UI/* %{buildroot}%{_datadir}/a2billing/agent
cp -LR  Signup/* %{buildroot}%{_datadir}/a2billing/signup

install -d %{buildroot}%{_localstatedir}/asterisk/agi-bin
install -d %{buildroot}%{_localstatedir}/asterisk/agi-bin/libs_a2billing
install -d %{buildroot}%{_localstatedir}/asterisk/agi-bin/libs_a2billing/adodb
cp -LR  A2Billing_AGI/*.php %{buildroot}%{_localstatedir}/asterisk/agi-bin/

# selectively install only the required php classes:
install A2Billing_AGI/libs_a2billing/Class.A2Billing.inc.php \
	A2Billing_AGI/libs_a2billing/index.php \
		%{buildroot}%{_localstatedir}/asterisk/agi-bin/libs_a2billing/

cp -LR  A2Billing_AGI/libs_a2billing/adodb/*.php %{buildroot}%{_localstatedir}/asterisk/agi-bin/libs_a2billing/adodb/
install -d %{buildroot}%{_localstatedir}/asterisk/sounds
cp -LR  addons/sounds/* %{buildroot}%{_localstatedir}/asterisk/sounds

cp -LR  DataBase/psql/* %{buildroot}%{_datadir}/a2billing/Database

install -d %{buildroot}%{_webappconfdir}
cat '-' > %{buildroot}%{_webappconfdir}/10_a2bagent.conf << EOF
Alias /agent "%{_datadir}/a2billing/agent"
<Directory "%{_datadir}/a2billing/agent" >
    Options Indexes MultiViews 
    #-FollowSymlinks
    Order deny,allow
    Allow from all
</Directory>
EOF

cat '-' > %{buildroot}%{_webappconfdir}/10_a2badmin.conf << EOF
Alias /a2badmin "%{_datadir}/a2billing/admin"
<Directory "%{_datadir}/a2billing/admin" >
    Options Indexes MultiViews FollowSymlinks
    Order deny,allow
    Allow from all
#    Allow from 127.0.0.1
</Directory>
EOF

cat '-' > %{buildroot}%{_webappconfdir}/10_a2bcustomer.conf << EOF
Alias /customer  "%{_datadir}/a2billing/customer"
<Directory "%{_datadir}/a2billing/customer" >
    Options Indexes MultiViews FollowSymlinks
    Order deny,allow
    Deny from all
    Allow from all
</Directory>
EOF

cat '-' > %{buildroot}%{_webappconfdir}/10_a2bsignup.conf << EOF
Alias /signup   "%{_datadir}/a2billing/signup"
<Directory "%{_datadir}/a2billing/signup" >
    Options Indexes MultiViews FollowSymlinks
    Order deny,allow
    Deny from all
    Allow from all
</Directory>
EOF


%clean
[ -n "%{buildroot}" -a "%{buildroot}" != / ] && rm -rf %{buildroot}

%files
%defattr(-,root,root)

%files config
%doc %{_datadir}/a2billing/LICENSE 
%doc %{_datadir}/a2billing/FEATURES_LIST
#this is wrong: /etc/asterisk may not be o+x
%attr(0640,asterisk,apache) %config(noreplace) %{_sysconfdir}/a2billing.conf

%files admin
%defattr(-,root,root)
%{_datadir}/a2billing/admin
%config(noreplace) %{_webappconfdir}/10_a2badmin.conf

%files customer
%defattr(-,root,root)
%{_datadir}/a2billing/customer
%config(noreplace) %{_webappconfdir}/10_a2bcustomer.conf

%files agent
%defattr(-,root,root)
%{_datadir}/a2billing/agent
%config(noreplace) %{_webappconfdir}/10_a2bagent.conf

%files signup
%defattr(-,root,root)
%{_datadir}/a2billing/signup
%config(noreplace) %{_webappconfdir}/10_a2bsignup.conf

%files AGI
%defattr(-,asterisk,root)
%{_localstatedir}/asterisk/agi-bin/
%{_localstatedir}/asterisk/sounds/

%files dbadmin
%defattr(-,asterisk,root)
%{_datadir}/a2billing/Database

# %verifyscript ... 