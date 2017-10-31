# Kaltura Platform Packages CI Project
See [Kaltura Install Packaging Project](https://github.com/kaltura/platform-install-packages) for the parent project.  

## Project Goal
The Continuous Integration system will be responsible for the automation of the following tasks:   

* Overall build of the [Kaltura platform packages](https://github.com/kaltura/platform-install-packages) against the master branch, release branches and approved pull-requests (for both nightly and stable releases).
* Pushing the packages to the install repositories.
* Perform a full Kaltura deployment on a test cluster.
* Perform automated testing of the installed server features via API calls and command line scripts, determining overall build stability for both clean install and version to version upgrades.
* Generate web-page build reports and email in case of fails.
* Distribute packaged/compiled API client libraries to respective language repositories.

## Why we need a CI system?
An automatic approach to the build, test and release process has many advantages, most prominently, it significantly reduces the time to release new packages and verifies that packages were fully tested before being used in production.
We've also listed some key advantages in our specific project -   

* Release more often, faster, and provide nightly builds for advanced platform testers.
* Ensure new commits do not break existing functionality on the master branch.
* Allow contributors to make changes and additions with a higher level of security, knowing pull-requests are tested as part of the whole system in production mode, before being merged.
* Provide elaborate platform test reports before entering official manual QA phase.

## Bootstrapping
The CI makes use of answer files to achieve an unattended deployment.
An answer file template for RPM based deployments can be obtained here:
https://github.com/kaltura/platform-install-packages/blob/master/doc/kaltura.template.ans

An answer file template for deb based deployments can be obtained here:
https://github.com/kaltura/platform-install-packages/blob/master/doc/install-kaltura-deb-based.md#unattended-installation

- Edit the templates to reflect your settings and place the file under /etc/kalt.ans on all machines you plan to use for running the CI. Bear in mind that this file contains sensitive info and should therefore only be readable and writable by super users.

- Create $BASE_DIR/rpm_cluster_members and $BASE_DIR/deb_cluster_members. These should contain a list of hosts, separated by newlines, like so:
```
my.machine0
my.machine1
my.machine2
```

- Use $BASE_DIR/csi.sql to create an SQLite3 DB under $BASE_DIR/db/csi.db. 

- Edit $BASE_DIR/run_sanity.sh and set the needed params. 

### Generating and testing API clients

If you wish to generate and test the API clients as part of the CI process: 
- In $BASE_DIR/run_sanity.sh, set TEST_CLIENTS to 'Y' and set CSI_CLIENT_GENERATING_HOST to the host you wish to generate the clients from.
- Edit clientlib_to_git_repo.rc so that it lists the GitHub repos you set up for hosting the generated clients.

*NOTE: The script expects these repos to be hosted on GitHub and at Kaltura, we use Travis CI for the actual testing.
That said, if you intend to use a different source control [or just Git but not GitHub] or a different CI service, this can be achieved by making minor changes to $BASE_DIR/clientlibs_test.sh.*


## The Test Suites
**All API calls and apps will be loaded over SSL.**

The following test cases will be run in the following order, on each cluster deployment (both clean and upgrade).
Nightly testing should run a complete regression coverage via API client libs, verifying the stability of the latest MASTER branch.   

1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png) Check space on / partition 
1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png) Check space on web partition
1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png) Check KDP3 version is correct by comparing KMC's config.ini with the actual last created KDP3 dir on disk.
1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png) Check KMC version is correct by comparing KMC's config.ini with the actual last created KDP3 dir on disk.
1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png) Check KDP3 version is correct by comparing KMC's config.ini with the actual last created HTML5 dir on disk.
1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png) Verify that all relevant processes (Apache, MySQL, Sphinx, batch, memcache, monit) are up and running on all machines in the cluster
1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png) Verify that all processes and crons are properly configured to run after system restart
1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png) Verify HTTPs call redirects for start page, KMC, Admin Console and testme. Perform curl request (with redirect follow) to each of the URLs, and test the response returned as expected:
    1. https://[DOMAIN]/  --- Verify Start Page
    1. https://[DOMAIN]/api_v3/testme/  --- Verify TestMe Console URL
    1. https://[DOMAIN]/index.php/kmc  --- Verify KMC URL
    1. https://[DOMAIN]/admin_console/  --- Verify Admin Console URL
    1. https://[DOMAIN]/apps/studio  --- Verify Universal Studio URL
1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png) Verify system restart behaviour (run 1 through 3 post restart)
1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png) Verify that processes (Apache, MySQL, Sphinx, batch, memcache) are being relaunched by monit after MANUAL kill (testing crash resurrection).
1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png) Verify new publisher account creation. Continue all following tests on this new partner account.
1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png) Verify profile ID creation.
1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png) Verify profile ID delete.
1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png) Test email logs for sent new publisher account activation email.
1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png) uiConf and file verifications - 
    1. Run through all the uiConfs in the database.
    1. For each uiConf, run through the uiConf object URLs AND inside the uiConf XML for all referenced file paths (swf, js, image files, etc.) and verify the existence of these files on disk.
    1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png) Check the kmc.swf and login.swf requests return 200
1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png)Verify simple transcoding: Upload video, see complete transcoding flow finished successfully.
1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png)Verify reconversion: reconvert an existing asset, see complete transcoding flow finished successfully.
1. Verify fallback transcoding: Rename the ffmpeg symlink. Upload video, see complete transcoding flow finished successfully. Rename the ffmpeg symlink back.
1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png) Verify clipping and trimming API
1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png) Verify clipapp index page loads
1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png) Run all client libraries and their respective unit-tests
1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png) Enable dropfolder plugin
1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png) Create a Local DropFolder 
1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png) Test Bulk Upload XML that includes custom metadata fields and thumbnails.
1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png) Setup email notification for new entry event
1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png) Setup HTTP notification for entry change event
1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png) Create a new Entry
1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png) Check mail logs to see if email was sent.
1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png) Analytics verification
1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png) Upload captions
1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png) Search string in captions and entry metadata
1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png)Check the report API to see the play count
1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png) Curl playManifest to that Entry WITH a valid sview KS, see that the video returns
1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png) Thumbnail API verification:
    1. *This test will have a stored prepared image to compare against*
    1. Call the thmbnail API for second 2 of the new entry
    1. Use ImageMagick to compare the returned image against the stored test imaged
1. ![test-created](http://kaltura.github.io/platform-install-packages/images/checkmark.png)Verify Red5 is working -  http://exchange.nagios.org/directory/Plugins/Software/FMS-monitor/details
1. Verify YouTube distribution (create profile, distribute entry, query for success)
1. Check the bandwidth report API to see bandwidth and storage counts
1. Verify KS Access Control:
1. Create an AC Profile with KS protection
1. Assign it to a Video Entry
1. Curl playManifest to that Entry without a KS, see that the video fails to return
1. Create a Local XML DropFolder, copy a file to the folder, test the file was successfully pulled in to Kaltura, transcoded and that the XML metadata exists.
1. Create a Remote Storage profile against an S3 Bucket, verify that content uploaded gets pushed to the S3 bucket.
1. Verify Player - Use http://phantomjs.org/ to run base tests against player embed, playlist embed, thumbnail embed, and common player scenarios (play, pause, seek)


## The Reports

#### Execution Time Benchmarks
For each step in the CI cycle, execution time measurements will be performed and saved in order to analyze platform trends over time. The following CI steps will measured:

1. Time it took to pull the code from git repositories.
1. Time it took to build packages.
1. Time it took to push packages to install repositories.
1. Time it took to install each package on the test clusters (clean and upgrade).
1. Time it took to run post-inst scripts per package.
1. Time to run each unit-test.
1. Aggregate time from pulling code till finish tests (complete cycle).

#### Web Reports

* Full test report available on a URL with the version-date combo.
* Header should show overall health status of the build - Percentage of fail/pass
* Per test, status (FAILED or PASSED), and if failed, show unit test error output. According to the following table:

| Test File     | Status        | Execution Time  | Details                           |
|:-------------:|:-------------:|:---------------:|:---------------------------------:|
| test_xxx      | PASSED        | 12ms            |                                   |
| test_yyy      | PASSED        | 100ms           |                                   |
| test_zzz      | FAILED        | 876912ms        | Output of what failed in the test |

#### Email Reports
Setup a mailing list for people to subscribe for reports via email. 3 types of emails:

1. If RPM layer failed packaging - email subject: [PACKAGING_FAILED] Kaltura Release - {CORE.VERSION} - {BUILD.DATE}
1. If any of the core unit tests failed - email subject: [CORE_FAILED] Kaltura Release - {HEALTH.PERCENT} - {CORE.VERSION} - {BUILD.DATE}
1. If all tests passed successfully - email subject: [BUILD_READY] Kaltura Release - {CORE.VERSION} - {BUILD.DATE}

The body of the email will be a full table report of the test suite (as defined above in web reports).

## Build Paths

* Nightly packages are built and saved into the /nightly/ folder.
* Pre-QA-Release packages are built and saved into the /release/ folder with respective release version and date.
 
## Post Successful Build - Client Libraries Distribution

* If Pre-QA-Release passes 100%, also distribute packaged/compiled client libraries to respective repositories: http://rubygems.org/ , https://www.npmjs.org/ , https://getcomposer.org/ https://pypi.python.org/ 


## License and Copyright Information
All code in this project is released under the [AGPLv3 license](http://www.gnu.org/licenses/agpl-3.0.html) unless a different license for a particular library is specified in the applicable library path. 

Copyright © Kaltura Inc. All rights reserved.

Authors [@jessp01](https://github.com/jessp01), [@zoharbabin](https://github.com/zoharbabin) and many others.
