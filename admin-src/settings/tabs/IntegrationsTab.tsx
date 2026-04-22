/**
 * Integrations settings tab — placeholder.
 *
 * @package
 */

import { __ } from '@wordpress/i18n';

const IntegrationsTab = () => {
	return (
		<div className="astrologer-admin__tab-content">
			<p>
				{ __(
					'Astrologer API supports integration with third-party plugins and themes via WordPress hooks. Use the astrologer_chart_calculated action to receive chart data in your custom code, or the astrologer_before_render filter to modify chart output.',
					'astrologer-api'
				) }
			</p>
		</div>
	);
};

export default IntegrationsTab;
