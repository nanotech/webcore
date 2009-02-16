WebCore
=======

WebCore is an attempt at creating a fast, usable, and powerful PHP 5
Model-View-Controller framework.

Getting Started
---------------

To generate a base application to start working with, use the setup script:

    webcore/script/setup.php app-name [/base/url]

I haven't made a way to install the setup script into your path yet, so
you'll have to type out its full path.

The first argument is the name of the application you want to create, and
the optional "base url" argument is the base path of the application in
it's url. If you leave the base path off, it defaults to `/`.

If you ran the script from inside your public web directory, you will be able
to access from a url like this:

    http://localhost/app-name/

Or if you added a base url:

    http://localhost/base/url/app-name/

If mod_rewrite is not enabled, you'll have to append `index.php/` to the url:

    http://localhost/app-name/index.php/

