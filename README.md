![maia stats](https://github.com/einheit/maia_mailguard_1.04a/blob/master/contrib/000-maia-stats.png "maia stats")

This repo was derived from the technion fork of Maia mailguard some years ago.

Our goal is not to add any new features, but to be good stewards of the maia mailguard 1.0 branch, maintain compatibility with current Linux distributions, and provide a quick installation process, for the common case, as we await the arrival of maia 2.0

Maia is flexible and scalable. It can be deployed in a number of configurations, from everything on a single container, VM or physical instance, to banks of MTAs, banks of maiad/spamassasin servers, dedicated clamav and database instances, dedicated web server instances for the management interface.

To get started, run "./install" and the script will try to detect the OS and offer the best option for installing on your system. 

Centos/RHEL versions 7 and 8 were the first supported platforms, then Debian 10, ubuntu 18.04 and ubuntu 20.04 were tested and verified. Other platforms will be tested and added as time allows. 

The script could very well fail to detect a supported OS, which, in and of itself is not a show stopper, as the install scripts are merely a convenience. It's safe to say that some or all maia components should be able to run on any Unix-like OS.

Contributed scripts for additional distros/platforms are welcome.

Updated Centos spamassassin rpms - https://github.com/einheit/maia-packages

-- 

The README from the original technion fork is below:

The original license has been replaced with a GPL license with the blessing of the project's original creator. See license.txt for further information.

This fork brings the project in line with current deployments and technology, such as newer versions of PHP.

Some features are known to be untested and probably broken, such as authentication mechanisms other than "internal".

There are some new requirements on PHP modules, these are easiest identified by running the standard configtest.php.

I would greatly appreciate an updated installation guide for this fork, if anyone wanted to contribute.

# maia_mailguard_1.04a
