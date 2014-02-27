Kaltura Platform Server CI Project
==================================

Project Goal
------------
The Continuous Integration system will be responsible for the automation of the following tasks:
Overall build of the Kaltura platform packages against the master branch, release tags and approved pull-requests (for both nightly and stable releases).
Pushing of the packages to the install repositories.
Perform a full Kaltura deployment on a test server.
Perform automated testing of the installed server features via API calls and command line scripts, determining overall build stability.
Generate and send build reports.

Why we need a CI system?
------------------------
An automatic approach to the build, test and release process has many advantages, most prominently, it significantly reduces the time to release new packages and verifies that packages were fully tested before being used in production.
We’ve also listed some key advantages in our specific project - 
Release more often, faster. And provide nightly builds for advanced platform testers.
Ensure new commits do not break existing functionality on the master branch.
Allow contributors to make changes and additions with a higher level of security, knowing they can test them as part of the whole system. And automatically run tests against pull-requests before these approved for merge.
Provide elaborate platform test reports before entering official manual QA phase.

CI Workflow
-----------
This will be the general flow of each CI run:
Versions of packages included in the new release will be updated in spec files.
Additional required steps for the upgrade [SQL alter scripts, additional token replacements, etc] will be added to the postinst according to need
The packages will be built
Packages are uploaded to the repository using an SSH key
Packages are distributed to Akamai
Following AMI instances are launched via EC2 CLI API:
Clean scenario: clean images of the following structure:
    2 fronts behind an HTTPs LB (a Linux machine running Apache configured to use the ProxyPass module)
    1 Admin Console front
    2 batch servers
    2 sphinx servers
    1 MySQL DB server (shall we also test replication?)
Upgrade scenario: images of the following roles with the previous version installed:
    2 fronts behind an HTTPs LB (a Linux machine running Apache configured to use the ProxyPass module)
    1 Admin Console front
    2 batch servers
    2 sphinx servers
    1 MySQL DB server (shall we also test replication?)
For both fresh and upgrade scenarios, sanitizing tests using the PHP5 API will include:
    Listing entries
    Uploading from CSV
    Uploading one entry of each type [image, audio, video]
    Wait 5 minutes and ensure all the entries are in status ready [2]
    More tests..

After the tests finished - publish a build and tests report, and if failed - send email to core-team. 
After all tests passed  SUCCESSFULLY - build and distribute the client libraries to known repositories (gem, npm, getcomposer.org, etc.)






