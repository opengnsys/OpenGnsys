# We need http://nsis.sourceforge.net/NSIS_Simple_Firewall_Plugin
# Copy inside the two x86_xxxxx folders inside nsis plugins folder
Name "OpenGnsys Agent"

# OpenGnsys Actor version
!define OGA_VERSION 1.1.0

# General Symbol Definitions
!define REGKEY "SOFTWARE\OGAgent"
!define VERSION ${OGA_VERSION}.0
!define COMPANY "OpenGnsys Project"
!define URL http://opengnsys.es

# MultiUser Symbol Definitions
!define MULTIUSER_EXECUTIONLEVEL Admin
#!define MULTIUSER_INSTALLMODE_DEFAULT_CURRENTUSER
!define MULTIUSER_INSTALLMODE_COMMANDLINE
!define MULTIUSER_INSTALLMODE_INSTDIR OGAgent
!define MULTIUSER_INSTALLMODE_INSTDIR_REGISTRY_KEY "${REGKEY}"
!define MULTIUSER_INSTALLMODE_INSTDIR_REGISTRY_VALUE "Path"

# MUI Symbol Definitions
#!define MUI_ICON "${NSISDIR}\Contrib\Graphics\Icons\orange-install.ico"
!define MUI_ICON "src\img\oga.ico"
!define MUI_FINISHPAGE_NOAUTOCLOSE
!define MUI_UNICON "src\img\oga.ico"
!define MUI_UNFINISHPAGE_NOAUTOCLOSE
!define MUI_LANGDLL_REGISTRY_ROOT HKLM
!define MUI_LANGDLL_REGISTRY_KEY ${REGKEY}
!define MUI_LANGDLL_REGISTRY_VALUENAME InstallerLanguage

# Included files
!include MultiUser.nsh
!include Sections.nsh
!include MUI2.nsh

# Reserved Files
!insertmacro MUI_RESERVEFILE_LANGDLL

# Variables
Var StartMenuGroup

# Installer pages
!insertmacro MUI_PAGE_WELCOME
!insertmacro MUI_PAGE_LICENSE src\license.txt
!insertmacro MUI_PAGE_DIRECTORY
!insertmacro MUI_PAGE_INSTFILES
!insertmacro MUI_PAGE_FINISH
!insertmacro MUI_UNPAGE_CONFIRM
!insertmacro MUI_UNPAGE_INSTFILES

# Installer languages
!insertmacro MUI_LANGUAGE English
!insertmacro MUI_LANGUAGE Spanish
!insertmacro MUI_LANGUAGE French
!insertmacro MUI_LANGUAGE German

# Installer attributes
BrandingText "OpenGnsys"
OutFile OGAgentSetup-${OGA_VERSION}.exe
InstallDir OGAgent
CRCCheck on
XPStyle on
ShowInstDetails hide
VIProductVersion "${VERSION}.0.0"
VIAddVersionKey /LANG=${LANG_ENGLISH} ProductName "OGAgent"
VIAddVersionKey /LANG=${LANG_ENGLISH} ProductVersion "${VERSION}"
VIAddVersionKey /LANG=${LANG_ENGLISH} CompanyName "${COMPANY}"
VIAddVersionKey /LANG=${LANG_ENGLISH} CompanyWebsite "${URL}"
VIAddVersionKey /LANG=${LANG_ENGLISH} FileVersion "${VERSION}"
VIAddVersionKey /LANG=${LANG_ENGLISH} FileDescription "OpenGnsys Agent installer"
VIAddVersionKey /LANG=${LANG_ENGLISH} LegalCopyright "(c) 2015 Virtual Cable S.L.U."
InstallDirRegKey HKLM "${REGKEY}" Path
ShowUninstDetails show

# Installer sections
Section -Main SEC0000
    SetShellVarContext all
    SetOutPath $INSTDIR
    SetOverwrite on
    File /r bin\*.*
    File vcredist_x86.exe
    WriteRegStr HKLM "${REGKEY}\Components" Main 1
SectionEnd

Section -post SEC0001
    SetShellVarContext current
    WriteRegStr HKLM "${REGKEY}" Path $INSTDIR
    SetOutPath $INSTDIR
    WriteUninstaller $INSTDIR\OGAgentUninstaller.exe
    SetOutPath $SMPROGRAMS\$StartMenuGroup
    CreateShortcut "$SMPROGRAMS\$StartMenuGroup\$(^UninstallLink).lnk" $INSTDIR\OGAgentUninstaller.exe
    WriteRegStr HKLM "SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\$(^Name)" DisplayName "$(^Name)"
    WriteRegStr HKLM "SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\$(^Name)" DisplayVersion "${VERSION}"
    WriteRegStr HKLM "SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\$(^Name)" Publisher "${COMPANY}"
    WriteRegStr HKLM "SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\$(^Name)" URLInfoAbout "${URL}"
    WriteRegStr HKLM "SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\$(^Name)" DisplayIcon $INSTDIR\OGAgentUninstaller.exe
    WriteRegStr HKLM "SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\$(^Name)" UninstallString $INSTDIR\OGAgentUninstaller.exe
    WriteRegStr HKLM "SOFTWARE\Microsoft\Windows\CurrentVersion\Run" OGAgentTool $INSTDIR\OGAgentUser.exe
    WriteRegDWORD HKLM "SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\$(^Name)" NoModify 1
    WriteRegDWORD HKLM "SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\$(^Name)" NoRepair 1
    ExecWait '"$INSTDIR\vcredist_x86.exe" /passive /norestart'
    # Add the application to the firewall exception list - All Networks - All IP Version - Enabled
    # SimpleFC::AddApplication "OpenGnsys Agent Service" "$INSTDIR\OGAgentService.exe" 0 2 "" 1
    # SimpleFC::AdvAddRule [name] [description] [protocol] [direction] 
    #  [status] [profile] [action] [application] [service_name] [icmp_types_and_codes] 
    #  [group] [local_ports] [remote_ports] [local_address] [remote_address]
    #
    SimpleFC::AdvAddRule "OpenGnsys Agent Firewall rules" "Firewall rules for OpenGnsys Agent interaction with broker." "6" "1" \
      "1" "7" "1" "$INSTDIR\OGAgentService.exe" "" "" \
      "" "" "" "" ""    
    Pop $0 ; return error(1)/success(0)
    # Install service
    nsExec::Exec /OEM "$INSTDIR\OGAgentService.exe --startup auto install" # Add service after installation
    # Update recovery options
    nsExec::Exec /OEM "$INSTDIR\OGAServiceHelper.exe" 
SectionEnd

# Macro for selecting uninstaller sections
!macro SELECT_UNSECTION SECTION_NAME UNSECTION_ID
    Push $R0
    ReadRegStr $R0 HKLM "${REGKEY}\Components" "${SECTION_NAME}"
    StrCmp $R0 1 0 next${UNSECTION_ID}
    !insertmacro SelectSection "${UNSECTION_ID}"
    GoTo done${UNSECTION_ID}
next${UNSECTION_ID}:
    !insertmacro UnselectSection "${UNSECTION_ID}"
done${UNSECTION_ID}:
    Pop $R0
!macroend

# Uninstaller sections
Section /o -un.Main UNSEC0000
    nsExec::Exec /OEM "$INSTDIR\OGAgentService.exe stop" # Stops the service prior uninstall
    nsExec::Exec /OEM "$INSTDIR\OGAgentService.exe remove" # Removes the service prior uninstall 
    Delete /REBOOTOK "$INSTDIR\*.*"
    DeleteRegValue HKLM "${REGKEY}\Components" Main
    DeleteRegValue HKLM "SOFTWARE\Microsoft\Windows\CurrentVersion\Run" OGAgentTool
SectionEnd

Section -un.post UNSEC0001
    # Remove application from the firewall exception list
    # SimpleFC::RemoveApplication "$INSTDIR\OGAgentService.exe"
    SimpleFC::AdvRemoveRule "OpenGnsys Agent Firewall rules"
    Pop $0 ; return error(1)/success(0)

    SetShellVarContext current
    StrCpy $StartMenuGroup "OpenGnsys Agent"
    DeleteRegKey HKLM "SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\$(^Name)"
    Delete /REBOOTOK "$SMPROGRAMS\$StartMenuGroup\$(^UninstallLink).lnk"
    Delete /REBOOTOK $INSTDIR\OGAgentUninstaller.exe
    DeleteRegValue HKLM "${REGKEY}" Path
    DeleteRegKey /IfEmpty HKLM "${REGKEY}\Components"
    DeleteRegKey /IfEmpty HKLM "${REGKEY}"
    RmDir /REBOOTOK $SMPROGRAMS\$StartMenuGroup
    SetShellVarContext all
    RmDir /REBOOTOK $INSTDIR
    SetRebootFlag true
    MessageBox MB_OK|MB_USERICON "Your system needs to reboot to complete uninstallation."
    Reboot # Reboot is needed after uninstalling, so new installs works fine
SectionEnd

# Installer functions
Function .onInit
    InitPluginsDir
    StrCpy $StartMenuGroup "OpenGnsys Agent"
    
    !insertmacro MUI_LANGDLL_DISPLAY
    !insertmacro MULTIUSER_INIT
FunctionEnd

# Uninstaller functions
Function un.onInit
    StrCpy $StartMenuGroup "OpenGnsys Agent"
    !insertmacro MUI_UNGETLANGUAGE
    !insertmacro MULTIUSER_UNINIT
    !insertmacro SELECT_UNSECTION Main ${UNSEC0000}
FunctionEnd

# Installer Language Strings
LangString ^UninstallLink ${LANG_ENGLISH} "Uninstall $(^Name)"
LangString ^UninstallLink ${LANG_SPANISH} "Desinstalar $(^Name)"
LangString ^UninstallLink ${LANG_FRENCH} "D�sinstaller $(^Name)"
LangString ^UninstallLink ${LANG_GERMAN} "deinstallieren $(^Name)"
