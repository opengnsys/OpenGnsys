#!/bin/bash
# Create macOS installation packages.
# Based on bomutils tutorail: http://bomutils.dyndns.org/tutorial.html

VERSION=1.1.0
AUTHOR="OpenGnsys Project"

# Create directories.
mkdir -p build && cd build
mkdir -p flat/base.pkg flat/Resources/en.lproj
mkdir -p root/Applications

# Copy application and script files.
cp -r ../../src root/Applications/OGAgent.app
cp -r ../scripts .

# Create plist file.
cat << EOT > root/Applications/OGAgent.app/OGAgent.plist
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
        <key>BuildAliasOf</key>
        <string>OGAgent</string>
        <key>BuildVersion</key>
        <value>$VERSION</value>
        <author>$AUTHOR</author>
</dict>
</plist>
EOT

# Add files in the base package.
( cd root && find . | cpio -o --format odc --owner 0:80 | gzip -c ) > flat/base.pkg/Payload

# Create PackageInfo file.
cat << EOT > flat/base.pkg/PackageInfo
<pkg-info format-version="2" identifier="es.opengnsys.ogagent.base.pkg" version="$VERSION" install-location="/" auth="root">
<payload installKBytes="$(du -k -s root)" numberOfFiles="$(find root | wc -l)"/>
  <scripts>
    <postinstall file="./postinstall"/>
  </scripts>
<bundle-version>
    <bundle id="es.opengnsys.ogagent" CFBundleIdentifier="es.opengnsys.ogagent" path="./Applications/OGAgent.app" CFBundleVersion="$VERSION"/>
</bundle-version>
</pkg-info>
EOT

# Compress the scripts folder.
( cd scripts && find . | cpio -o --format odc --owner 0:80 | gzip -c ) > flat/base.pkg/Scripts

# Create BOM file.
mkbom -u 0 -g 80 root flat/base.pkg/Bom

# Create Distribution file.
cat << EOT > flat/Distribution
<?xml version="1.0" encoding="utf-8"?>
<installer-script minSpecVersion="1.000000" authoringTool="com.apple.PackageMaker" authoringToolVersion="3.0.3" authoringToolBuild="174">
    <title>OGAgent 1.1.0</title>
    <options customize="never" allow-external-scripts="no"/>
    <domains enable_anywhere="true"/>
    <installation-check script="pm_install_check();"/>
    <script>function pm_install_check() {
  if(!(system.compareVersions(system.version.ProductVersion,'10.5') >= 0)) {
    my.result.title = 'Failure';
    my.result.message = 'You need at least Mac OS X 10.5 to install OGAgent.';
    my.result.type = 'Fatal';
    return false;
  }
  return true;
}
</script>
    <choices-outline>
        <line choice="choice1"/>
    </choices-outline>
    <choice id="choice1" title="base">
        <pkg-ref id="es.opengnsys.ogagent.base.pkg"/>
    </choice>
    <pkg-ref id="es.opengnsys.ogagent.base.pkg" installKBytes="$(du -k -s root)" version="$VERSION" auth="Root">#base.pkg</pkg-ref>
</installer-script>
EOT

# Create new Xar application archive.
rm -f ../../../OGAgentInstaller-$VERSION.pkg
( cd flat && xar --compression none -cf "../../../OGAgentInstaller-$VERSION.pkg" * )

