import Blogs from "./pages/dashboard/Blogs";
import Help from "./pages/Help";
import License from "./pages/License";
import { render } from '@wordpress/element';

if( window.location.pathname.includes('index.php') || ! window.location.pathname.includes('php') ) {
	render(<Blogs />, document.getElementById('cx-posts'));
}

if( window.location.search.includes('autoload-manager-help') ) {
	render(<Help />, document.getElementById('autoload-manager_help'));
}

if( window.location.search.includes('autoload-manager-license') ) {
	render(<License />, document.getElementById('autoload-manager_license'));
}