# Release Procedure

The following steps should be taken when creating a new release:

1. Verify the last commit on master has a successful build.
1. Document the changes of the current release in the `CHANGELOG.md`.
1. Verify that a release based on master can be built (using `bin/makeRelease.sh master`)
1. Create the release in the Github interface. Ensure the correct version number is used and use the prepared release
   notes.
1. Build a new release tarball based on the created tag using `bin/makeRelease.sh`  
   This creates two files: OpenConext-user-lifecycle-{version}.tar.gz and OpenConext-user-lifecycle-{version}.sha, both can be
   found in `~/Releases`.
1. Edit the release in the Github interface and upload the generated OpenConext-user-lifecycle-{version}.tar.gz and
   OpenConext-user-lifecycle-{version}.sha. Save the updated release.
1. Communicate the latest release.

All done.
