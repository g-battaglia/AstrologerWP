/**
 * Capabilities settings tab — read-only reference table.
 *
 * @package Astrologer\Api
 */

import { __ } from '@wordpress/i18n';

const CAPABILITIES = [
	'astrologer_manage_settings',
	'astrologer_view_charts',
	'astrologer_create_charts',
	'astrologer_edit_charts',
	'astrologer_delete_charts',
];

const ROLES = [ 'Administrator', 'Editor', 'Author', 'Subscriber' ];

const ROLE_CAPS: Record< string, string[] > = {
	Administrator: [
		'astrologer_manage_settings',
		'astrologer_view_charts',
		'astrologer_create_charts',
		'astrologer_edit_charts',
		'astrologer_delete_charts',
	],
	Editor: [
		'astrologer_view_charts',
		'astrologer_create_charts',
		'astrologer_edit_charts',
	],
	Author: [ 'astrologer_view_charts', 'astrologer_create_charts' ],
	Subscriber: [ 'astrologer_view_charts' ],
};

const CapabilitiesTab = () => {
	return (
		<div className="astrologer-admin__tab-content">
			<p>
				{ __(
					'The following capabilities are assigned to WordPress roles. Capabilities are managed via code and cannot be changed from this screen.',
					'astrologer-api'
				) }
			</p>

			<table className="widefat fixed striped">
				<thead>
					<tr>
						<th>{ __( 'Capability', 'astrologer-api' ) }</th>
						{ ROLES.map( ( role ) => (
							<th key={ role }>{ role }</th>
						) ) }
					</tr>
				</thead>
				<tbody>
					{ CAPABILITIES.map( ( cap ) => (
						<tr key={ cap }>
							<td>
								<code>{ cap }</code>
							</td>
							{ ROLES.map( ( role ) => (
								<td key={ role }>
									{ ROLE_CAPS[ role ]?.includes( cap )
										? '✓'
										: '—' }
								</td>
							) ) }
						</tr>
					) ) }
				</tbody>
			</table>
		</div>
	);
};

export default CapabilitiesTab;
