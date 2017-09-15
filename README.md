# sierra-actions
Add forms with actions to Sierra WebPAC.

## Background
The unique materials in the special collection libraries at BGSU ([Music Library and Bill Shurk Sound Archives](http://www.bgsu.edu/library/music.html), [Browne Popular Culture Library](http://www.bgsu.edu/library/pcl.html), and the [Center for Archival Collections](http://www.bgsu.edu/library/cac.html)) contain treasure troves of materials for researchers. Until recently, all collection use was tracked using paper registration forms but with the advent of the ACA/PCA's (American Culture Association/Popular Culture Association) [Popular Culture Summer Research Institute at BGSU](http://pcaaca.org/educatio/pcaaca-research-workshop/) in 2016, closed stack special collections experienced intensive use in conjunction with the Institute that overwhelmed the paper processes of the past and were unsustainable!

The special collections departments worked with Access Services and Library Information Technology Services to find a way to use Sierra and enhancements to the local OPAC to improve patron experience, automate collection of usage statistics, strengthen security, and streamline service point workflows. This project expanded to support collaboration between the [Curriculum Resource Center](http://www.bgsu.edu/library/crc.html) and the [BGSU Firelands Library](http://www.firelands.bgsu.edu/library.html).

The resulting code from the project is shared in this repository. While it is specific to BGSU's catalog and collections, it can be altered to suit the needs of other libraries as described below. There are two primary parts: a Javascript file which adds buttons to items in the bib display of Sierra WebPAC, and a PHP web application which provides forms to handle the actions of those buttons.

## Javascript
The PHP application generates a Javascript file based on the configuration described below. This file is available in the public directory at `/button.js`. For example, if the application is installed on a path named `actions` on the server `example.edu`, the Javascript may be referenced with:
```
<script src="https://example.edu/actions/button.js"></script>
```

The Javascript file depends upon [jQuery](https://jquery.com/) being available, and must be loaded after jQuery is loaded. At BGSU, this Javascript is added to the `screens/botlogo.html` file for the Sierra WebPAC. Since it looks for particular information only available on detailed item records, it should be safe to include on other pages.

### BGSU's Javascript as Example
You may want to use your own application to handle applications instead of the version offered by this project. In that case, you may be interested in reviewing BGSU's copy of the Javascript file for adaption to your own system. It is online at:
https://lib.bgsu.edu/catalog/actions/button.js


