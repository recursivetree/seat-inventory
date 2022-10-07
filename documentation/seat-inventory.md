# seat-inventory
## What is seat-inventory
This plugin aims to make the life of corporations and alliances easier by helping to manage contracts and corporation hangars. It loads contracts and corporation hangars from the ESI to make a list of what we have. Additionally, you can define what you need(e.g. a doctrine ship) and where you need it(staging). The plugin compares it to what you have and shows you how many times you have your doctrine ship stocked. Additionally, it shows you what's missing so you can restock.

Some features:

* notifications if your stocks run low
* dashboards to monitor doctrines consisting out of multiple ship types. You can even have one ship in multiple doctrine dashboards
* create stocks from fits from seat-fitting, changes through seat-fitting are reflected
* export missing items to seat-alliance-industry
* priorities: If there aren't enough items, preferably allocate them to high priority ships like a home-defence doctrine
* much more

**This guide is still work in progress!**


## Getting Started
This is a guide on how to get started with seat-inventory.

### Installation
The first step is to install seat-inventory. You can follow the normal steps to install seat plugins, as described [here](https://eveseat.github.io/docs/community_packages/). The package name is `recursivetree/seat-inventory`

### Configuration

#### Permissions
You can configure edit and view access over normal seat permissions under *Settings/Access Management*

##### View Inventory
Allows you to view the seat-inventory pages and the data on this page. As of right now, the UI still shows all buttons as if you have edit permissions, but it fails as soon as you press submit.

##### Edit Inventory
Allows you to change the configuration and everything else which is persisted on the server.

#### Plugin Configuration
Open *Inventory Management/Tracking*. On this page, you can configure from where inventory data will be loaded. You can add corporations and alliances. If you add a corporation, it's assets in the coporation hangars and corporation contracts will be tracked. Tracking an alliance only tracks alliance contracts. With alliances, you can choose if you want to track member corporations too.

After changing the tracking settings, you might need to wait up to 2 ESI cycles until theythe changes are fully reflected. Besides that, the plugin is ready to be used.

## The Inventory Browser
The inventory browser allows you to, as the name suggest, browse through all assets from the sources you configured under the tracking page.

You can use the filter to specify a single item type you look for and where you search for the items.

To the right, you see a grey text which describes the location of the item. It consists of the name of the hangar/ship/container and the station/structure.

The plugin currently doesn't distinguish between the different corporation hangars in a corporation office. Instead, it treats it like all items would be in one container.

Fitted ships in a corporation hangar are treated as a seperate source and don't appear inside the corporation hangar source. Instead, they apppear with their ship name in the station of the corporation hangar.

## The Dashboard
This is the heart of seat-inventory.

Once you open the dashboard under *Inventory management/Dashboard*, you should see three parts, from top to bottom:

1. A location filter. More on it later
2. A row of buttons
3. One or more extendable group. If you just installed the module, you should see a category `Default Group`. (On older installs, it might be named differently)

### The Buttons

#### Update
Reloads all data. You need this if two persons work on the dashboard at the same time, so you can load each other's changes. It is also useful if you change a stock and want to load the new data.

#### Deliveries
Opens the deliveries popup. Deliveries allow you to add items that don't really exist to the item sources. My corporation uses it to mark items that are being produced or shipped to staging as already there, so they dissappear from the list of missing items.

#### Collapse All
Collapses all open groups

#### Expand All
Expands all groups

#### Add Stock
Allows you to add a new stock. A stock is a definition of which and how many items should be stored where. seat-inventory then calculates what's missing or shows other statistics.

#### Add Group
Creates a new group. Groups allow you to, well, group stocks into packages which are displayed together. They can be used to for example have all ships of a doctrine together on one view.

### The Location Filter
When you have a lot of stock in different location, it can be quite troublesome to see which stocks are in which locations. When you enter a location in the filter, stock which aren't there will be faded out grey allowing you to focus on the remaining ones at the specified location.

## Stocks
A stock is the basic unit to represent demand for items in seat-inventory. Each stock has a name, location, quantity and a few more properties. If you use seat-inventory to monitor doctrines and other corp-provided ships, you usually create one stock per ship of the doctrine.

### Create a stock
Press the **+ Stock** button at the top of the page. A popup should open asking you to input some data.

There are 3 types of stocks:

* **Mutlibuy Stocks:** Enter the items in the form of the multibuy text exported from the multibuy window ingame
* **Fit:** Enter a fit to extract the items from. The fit will be converted to a multibuy item list by seat-inventory, so if you end up editing your stock after creating it, it will appear as a multibuy.
* **Fitting Plugin:** You can also create stocks based on fittings from seat-fitting, if the seat-fitting plugin is installed. If the fitting in seat-fitting changes, the changes will be reflected in seat-inventory.

Depending on the type, you have to input you fit, your items, or select a fitting from seat-fitting.

When you have a multibuy stock, you have to enter a name. For fits, it will be extracted from the fit name.

The following properties are common to all stock types:

* **Amount:** How many times do you want to have this stock stocked? If you want n stabbers, you enter the fit of the stabber as described above and n in amount.
* **Warning Threshold:** When the effectively available amount falls below this value, this stock will be highlighted red. Additionally, a notification will be sent when configured to.
* **Location:** Where the items of this stock should be stored at
* **Priority:** When there aren't enough items to cover all stocks, the stock with the highest priority will receive them first

Press save to create the stock.

Note: Stocks won't appear anywhere until you add them to a group. They will still be considered for availability computation even when not in a group.

## Groups
### Creating a Group
Press the **+ Group** button at the top. A popup should open asking for a name. Below it you can expand the stocks section and add/remove stocks to the group.
After saving, the group should appear on the dashboard page.

### Using Groups
When you expand a group, a card for each stock in the group appears. It contains the most important information about the stock like location, priority and availability in various location. You can edit a stock with the pen button and show even more detailed information with the info button.

If you want to modify a group, there is an edit button next to the expand/collapse button. You have the same options like when you create a group.

A stock can be in multiple groups at the same time. This can be useful if you have two doctrines that share a ship.

### Filters
Filters can automatically put stocks into groups.

## Deliveries

## Notifications
There are discord and mail notification available. You can configure them like all other seat notifications.

