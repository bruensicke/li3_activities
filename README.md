# li3_activities

Lithium library that allows tracking of various in-app events with additional data.

## Installation

Add a submodule to your li3 libraries:

	git submodule add git@github.com:bruensicke/li3_activities.git libraries/li3_activities

and activate it in you app (config/bootstrap/libraries.php), of course:

	Libraries::add('li3_activities');

## Usage

	Activity::track('sync_complete', $data);

## Credits

* [li3](http://www.lithify.me)

