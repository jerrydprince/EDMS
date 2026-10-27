// components.js
// Icon Library
import { Icon } from "@iconify/vue";

import Button from "primevue/button";
import Panel from "primevue/panel";
import InputText from "primevue/inputtext";
import Calendar from 'primevue/calendar';
import Menu from "primevue/menu";
import Tree from "primevue/tree";
import Timeline from "primevue/timeline";
import Dialog from "primevue/dialog";
import Divider from "primevue/divider";
import Textarea from "primevue/textarea";
import Toast from "primevue/toast";
import TabMenu from "primevue/tabmenu";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import ColumnGroup from "primevue/columngroup";   // optional
import Row from "primevue/row";                   // optional
import Dropdown from "primevue/dropdown";
import InputSwitch from "primevue/inputswitch";
import TabView from "primevue/tabview";
import TabPanel from "primevue/tabpanel";
import Checkbox from "primevue/checkbox";
import Sidebar from "primevue/sidebar";
import TieredMenu from "primevue/tieredmenu";
import Editor from 'primevue/editor'
import Chart from 'primevue/chart';
import VueApexCharts from "vue3-apexcharts";
import Tooltip from 'primevue/tooltip';


export function registerGlobalComponents(app) {
  app.directive('tooltip', Tooltip);
  app.component("Button", Button);
  app.component("Panel", Panel);
  app.component("InputText", InputText);
  app.component("Menu", Menu);
  app.component("Tree", Tree);
  app.component("Timeline", Timeline);
  app.component("Icon", Icon);
  app.component("Dialog", Dialog);
  app.component("Divider", Divider);
  app.component("Textarea", Textarea);
  app.component("Toast", Toast);
  app.component("DataTable", DataTable);
  app.component("Column", Column);
  app.component("ColumnGroup", ColumnGroup);
  app.component("Row", Row);
  app.component("TabMenu", TabMenu);
  app.component("Dropdown", Dropdown);
  app.component("InputSwitch", InputSwitch);
  app.component("TabView", TabView);
  app.component("TabPanel", TabPanel);
  app.component("Checkbox", Checkbox);
  app.component("Sidebar", Sidebar);
  app.component('TieredMenu', TieredMenu);
  app.component('Calendar', Calendar);
  // Heavy components removed from global registry to optimize bundle size:
  // app.component('Editor',Editor);
  // app.component('Chart',Chart);
  // app.component('Apexcharts',VueApexCharts)
}
