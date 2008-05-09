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

Requires:	%{name}-admin
Requires:	%{name}-customer
Requires:	%{name}-AGI

Requires:	postgresql >= 8.2.5
Requires:	php-pgsql
Requires:	php-gettext


%description
Asterisk2Billing is a frontend to the asterisk PBX,
raising it to a full telephony + billing platform.

This is a metapackage that contains all necessary elements
to run a2billing on a single server.


%package admin
Summary:	Administrator web interface
Group:		System/Servers
Requires:	apache-base >= 2.2.4
Requires:	apache-mod_ssl
Requires:	apache-mod_php >= 5.2.1

%description admin
The administrator web-interface to a2billing.

%package customer
Summary:	Customer web interface
Group:		System/Servers
Requires:	apache-base >= 2.2.4
Requires:	apache-mod_ssl
Requires:	apache-mod_php >= 5.2.1

%description customer
The web-interface for retail customers

%package agent
Summary:	Agent web interface
Group:		System/Servers
Requires:	apache-base >= 2.2.4
Requires:	apache-mod_ssl
Requires:	apache-mod_php >= 5.2.1


%description agent
Callshop (agent) web-interface.


%package signup
Summary:	Signup web interface
Group:		System/Servers
Requires:	apache-base >= 2.2.4
Requires:	apache-mod_ssl
Requires:	apache-mod_php >= 5.2.1


%description signup
Web signup pages for Asterisk2Billing.

%package AGI
Summary:	Asterisk interface
Group:		System/Servers
Requires:	asterisk >= 1.4.19
Requires:	php-pcntl

%description AGI
This package provides the necessary files for an asterisk server.

%package dbadmin
Summary:	Database files and scripts
Group:		System/Servers
Requires:	cron

%description dbadmin
Install this package into some machine that is client to the
database. Then, the database for %{name} can be built from that
host.
Additionally, maintenance tasks can be performed from that host.

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
rm -rf common/lib/adodb/contrib
install -d %{buildroot}%{_datadir}/a2billing
install -d %{buildroot}%{_datadir}/a2billing/a2badmin
install -d %{buildroot}%{_datadir}/a2billing/customer
install -d %{buildroot}%{_datadir}/a2billing/agent
install -d %{buildroot}%{_datadir}/a2billing/signup
install -d %{buildroot}%{_datadir}/a2billing/Database

cp -LR  A2Billing_UI/* %{buildroot}%{_datadir}/a2billing/a2badmin
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

install -d %{buildroot}%{_sysconfdir}/http/conf/webapps.d
cat '-' > %{buildroot}%{_sysconfdir}/http/conf/webapps.d/10_a2bagent.conf << EOF
Alias /agent "%{_datadir}/a2billing/agent"
<Directory "%{_datadir}/a2billing/agent" >
    Options Indexes MultiViews 
    #-FollowSymlinks
    Order deny,allow
    Allow from all
</Directory>
EOF

cat '-' > %{buildroot}%{_sysconfdir}/http/conf/webapps.d/10_a2badmin.conf << EOF
Alias /a2badmin "%{_datadir}/a2billing/admin"
<Directory "%{_datadir}/a2billing/admin" >
    Options Indexes MultiViews FollowSymlinks
    Order deny,allow
    Allow from all
#    Allow from 127.0.0.1
</Directory>
EOF

cat '-' > %{buildroot}%{_sysconfdir}/http/conf/webapps.d/10_a2bcustomer.conf << EOF
Alias /customer  "%{_datadir}/a2billing/customer"
<Directory "%{_datadir}/a2billing/customer" >
    Options Indexes MultiViews FollowSymlinks
    Order deny,allow
    Deny from all
    Allow from all
</Directory>
EOF

cat '-' > %{buildroot}%{_sysconfdir}/http/conf/webapps.d/10_a2bsignup.conf << EOF
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

%files admin
%defattr(-,root,root)
%{_datadir}/a2billing/a2badmin
%config(noreplace) %{_sysconfdir}/http/conf/webapps.d/10_a2badmin.conf

%files customer
%defattr(-,root,root)
%{_datadir}/a2billing/customer
%config(noreplace) %{_sysconfdir}/http/conf/webapps.d/10_a2bcustomer.conf

%files agent
%defattr(-,root,root)
%{_datadir}/a2billing/agent
%config(noreplace) %{_sysconfdir}/http/conf/webapps.d/10_a2bagent.conf

%files signup
%defattr(-,root,root)
%{_datadir}/a2billing/signup
%config(noreplace) %{_sysconfdir}/http/conf/webapps.d/10_a2bsignup.conf

%files AGI
%defattr(-,asterisk,root)
%{_localstatedir}/asterisk/agi-bin/
%{_localstatedir}/asterisk/sounds/

%files dbadmin
%defattr(-,asterisk,root)
%{_datadir}/a2billing/Database

# %verifyscript ... 