export const adminLteConf = {
  skin: 'blue',
  // isSidebarLeftCollapsed: false,
  // isSidebarLeftExpandOnOver: false,
  // isSidebarLeftMouseOver: false,
  // isSidebarLeftMini: true,
  // sidebarRightSkin: 'dark',
  // isSidebarRightCollapsed: true,
  // isSidebarRightOverContent: true,
  // layout: 'normal',
  sidebarLeftMenu: [
    {label: 'MAIN NAVIGATION', separator: true},
    {label: 'ous', route: 'app/ous', iconClasses: 'fa fa-th'},
    {label: 'images', route: '/app/images', iconClasses: 'fa fa-cubes'},
    {label: 'repositories', route: 'app/repositories', iconClasses: 'fa fa-database'},
    {label: 'hardware', route: 'app/hardware', iconClasses: 'fa fa-server'},
    {label: 'software', route: 'app/software', iconClasses: 'fa fa-archive'},
    {label: 'menus', route: 'app/menus', iconClasses: 'fa fa-file-text-o',
      pullRights: [{text: 'comming_soon', classes: 'label pull-right bg-green'}]},
    {label: 'commands', route: 'app/commands', iconClasses: 'fa fa-terminal'},
    {label: 'netboot_templates', route: 'app/netboots', iconClasses: 'fa fa-book'},
  ]
};
