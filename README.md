This repo was originally derived from the technion fork of Maia mailguard.

We've held off on certain changes e.g. scrypt, which we feel would be better suited to a version 1.1 release. Our goal is to obtain a stable, working install of the updated mailguard 1.0 branch, with a quick (for the common case) installation process.

The initial target platform will be Centos 7 on openvz, due to the ease and speed of deployment. 

It should be easy to adapt to Suse or Debian-based dstros, but the 
initial focus on Centos 7, and openvz containers is the target platform.

Scripts to install this mailguard release on other Linux distros wll be 
happily accepted. In the meantime, it can be installed on other distros 
via the manual procedure.

Instructions: (this will evolve as we test and refine the process)

For Centos 7, run the script "install-centos7.sh"

For all other distros, one can puzzle through the manual procedure in the
contrib directory, which, though written for maia 1.0.2, which used
amavisd-new, replaced by maiad in the current version, still contains
the gist of what is needed. It will eventually be brought up to date
as well. 

Contributions are welcome.

-- 

The README from the original technion fork is below:

The original license has been replaced with a GPL license with the blessing of the project's original creator. See license.txt for further information.

This fork brings the project in line with current deployments and technology, such as newer versions of PHP.

Some features are known to be untested and probably broken, such as authentication mechanisms other than "internal".

There are some new requirements on PHP modules, these are easiest identified by running the standard configtest.php.

I would greatly appreciate an updated installation guide for this fork, if anyone wanted to contribute.

# maia_mailguard_1.04a
