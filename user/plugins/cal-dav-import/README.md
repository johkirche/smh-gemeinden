# Cal Dav Import Plugin

**This README.md file should be modified to describe the features, installation, configuration, and general usage of this plugin.**

The **Cal Dav Import** Plugin is for [Grav CMS](http://github.com/getgrav/grav). The plugin imports external CalDAV events as separate pages

## Installation

Installing the Cal Dav Import plugin can be done in one of two ways. The GPM (Grav Package Manager) installation method enables you to quickly and easily install the plugin with a simple terminal command, while the manual method enables you to do so via a zip file.

### GPM Installation (Preferred)

The simplest way to install this plugin is via the [Grav Package Manager (GPM)](http://learn.getgrav.org/advanced/grav-gpm) through your system's terminal (also called the command line).  From the root of your Grav install type:

    bin/gpm install cal-dav-import

This will install the Cal Dav Import plugin into your `/user/plugins` directory within Grav. Its files can be found under `/your/site/grav/user/plugins/cal-dav-import`.

### Manual Installation

To install this plugin, just download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`. Then, rename the folder to `cal-dav-import`. You can find these files on [GitHub](https://github.com/3177158/grav-plugin-cal-dav-import) or via [GetGrav.org](http://getgrav.org/downloads/plugins#extras).

You should now have all the plugin files under

    /your/site/grav/user/plugins/cal-dav-import
	
> NOTE: This plugin is a modular component for Grav which requires [Grav](http://github.com/getgrav/grav) and the [Error](https://github.com/getgrav/grav-plugin-error) and [Problems](https://github.com/getgrav/grav-plugin-problems) to operate.

### Admin Plugin

If you use the admin plugin, you can install directly through the admin plugin by browsing the `Plugins` tab and clicking on the `Add` button.

## Configuration

Before configuring this plugin, you should copy the `user/plugins/cal-dav-import/cal-dav-import.yaml` to `user/config/plugins/cal-dav-import.yaml` and only edit that copy.

Here is the default configuration and an explanation of available options:

```yaml
enabled: true
```

Note that if you use the admin plugin, a file with your configuration, and named cal-dav-import.yaml will be saved in the `user/config/plugins/` folder once the configuration is saved in the admin.

## Usage

**Describe how to use the plugin.**

## Credits

**Did you incorporate third-party code? Want to thank somebody?**

## To Do

- [ ] Future plans, if any

