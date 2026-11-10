/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

const EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

/**
 * @param {Object} field Field schema.
 * @param {*}      value Field value.
 * @return {string} Error message, or '' when valid.
 */
export function validateField( field, value ) {
	switch ( field.type ) {
		case 'multiselect':
			return Array.isArray( value ) && value.length
				? ''
				: __(
						'Choose at least one value.',
						'restricted-shipping-and-payment-for-woocommerce'
				  );
		case 'text':
			return value && String( value ).trim()
				? ''
				: __(
						'Enter a value.',
						'restricted-shipping-and-payment-for-woocommerce'
				  );
		case 'email':
			return EMAIL.test( String( value || '' ).trim() )
				? ''
				: __(
						'Enter a valid email address.',
						'restricted-shipping-and-payment-for-woocommerce'
				  );
		case 'number':
		case 'money':
			return value !== '' &&
				value !== undefined &&
				! isNaN( Number( value ) ) &&
				Number( value ) >= 0
				? ''
				: __(
						'Enter a number of 0 or more.',
						'restricted-shipping-and-payment-for-woocommerce'
				  );
		default:
			return '';
	}
}

/**
 * @param {Object}   state     Editor state.
 * @param {Object[]} ruleTypes Rule type definitions.
 * @return {{targets: string, rules: string, ruleErrors: Object[], count: number}} Errors.
 */
export function validate( state, ruleTypes ) {
	const errors = { targets: '', rules: '', ruleErrors: [], count: 0 };

	if ( ! state.targets.length ) {
		errors.targets =
			state.context === 'payment'
				? __(
						'Choose at least one payment gateway.',
						'restricted-shipping-and-payment-for-woocommerce'
				  )
				: __(
						'Choose at least one shipping method.',
						'restricted-shipping-and-payment-for-woocommerce'
				  );
		errors.count++;
	}

	if ( ! state.rules.length ) {
		errors.rules = __(
			'Add at least one rule.',
			'restricted-shipping-and-payment-for-woocommerce'
		);
		errors.count++;
	}

	errors.ruleErrors = state.rules.map( ( rule ) => {
		const type = ruleTypes.find( ( t ) => t.type === rule.rule_type );
		const ruleErrors = {};
		if ( ! type ) {
			ruleErrors.rule_type = __(
				'Choose a rule type.',
				'restricted-shipping-and-payment-for-woocommerce'
			);
			errors.count++;
			return ruleErrors;
		}
		type.fields.forEach( ( field ) => {
			const message = validateField( field, rule[ field.key ] );
			if ( message ) {
				ruleErrors[ field.key ] = message;
				errors.count++;
			}
		} );
		return ruleErrors;
	} );

	return errors;
}
