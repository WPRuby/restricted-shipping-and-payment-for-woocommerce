/**
 * WordPress dependencies
 */
import { Button, SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import ValueField from './value-field';

/**
 * A new rule of the given type, with its first operator and empty values.
 *
 * @param {Object} ruleType Rule type definition.
 * @return {Object} Rule.
 */
export function newRule( ruleType ) {
	const rule = {
		rule_type: ruleType.type,
		operator: ruleType.operators[ 0 ]?.value || '',
	};
	ruleType.fields.forEach( ( field ) => {
		rule[ field.key ] = field.type === 'multiselect' ? [] : '';
	} );
	return rule;
}

/**
 * One rule: type, operator and value fields.
 *
 * @param {Object}   props
 * @param {Object}   props.rule
 * @param {Object[]} props.ruleTypes
 * @param {Object}   props.errors      field key => message.
 * @param {Object}   props.knownLabels rule type => field key => value => label.
 * @param {string}   props.currency
 * @param {boolean}  props.isFirst
 * @param {boolean}  props.isLast
 * @param {Function} props.onChange
 * @param {Function} props.onRemove
 * @param {Function} props.onMove      Called with -1 or 1.
 */
export default function RuleRow( {
	rule,
	ruleTypes,
	errors = {},
	knownLabels,
	currency,
	isFirst,
	isLast,
	onChange,
	onRemove,
	onMove,
} ) {
	const ruleType = ruleTypes.find( ( type ) => type.type === rule.rule_type );

	const changeType = ( type ) => {
		const next = ruleTypes.find( ( t ) => t.type === type );
		onChange( { ...newRule( next ), _key: rule._key } );
	};

	return (
		<div className="rspw-rule">
			<div className="rspw-rule__fields">
				<SelectControl
					className="rspw-rule__type"
					label={ __(
						'Rule',
						'restricted-shipping-and-payment-for-woocommerce'
					) }
					value={ ruleType ? rule.rule_type : '' }
					options={ [
						...( ruleType
							? []
							: [
									{
										value: '',
										label: __(
											'Select…',
											'restricted-shipping-and-payment-for-woocommerce'
										),
										disabled: true,
									},
							  ] ),
						...ruleTypes.map( ( type ) => ( {
							value: type.type,
							label: type.label,
						} ) ),
					] }
					onChange={ changeType }
					help={
						errors.rule_type ? (
							<span className="rspw-field-error">
								{ errors.rule_type }
							</span>
						) : undefined
					}
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
				{ ruleType && (
					<SelectControl
						className="rspw-rule__operator"
						label={ __(
							'Operator',
							'restricted-shipping-and-payment-for-woocommerce'
						) }
						value={ rule.operator }
						options={ ruleType.operators }
						onChange={ ( operator ) =>
							onChange( { ...rule, operator } )
						}
						__nextHasNoMarginBottom
						__next40pxDefaultSize
					/>
				) }
				{ ruleType &&
					ruleType.fields.map( ( field ) => (
						<div
							className={ `rspw-rule__value rspw-rule__value--${ field.type }` }
							key={ field.key }
						>
							<ValueField
								field={ field }
								value={ rule[ field.key ] }
								ruleType={ rule.rule_type }
								knownLabels={
									knownLabels?.[ rule.rule_type ]?.[
										field.key
									] || {}
								}
								currency={ currency }
								error={ errors[ field.key ] }
								onChange={ ( value ) =>
									onChange( {
										...rule,
										[ field.key ]: value,
									} )
								}
							/>
						</div>
					) ) }
			</div>
			<div className="rspw-rule__actions">
				<Button
					icon="arrow-up-alt2"
					label={ __(
						'Move up',
						'restricted-shipping-and-payment-for-woocommerce'
					) }
					size="small"
					disabled={ isFirst }
					onClick={ () => onMove( -1 ) }
				/>
				<Button
					icon="arrow-down-alt2"
					label={ __(
						'Move down',
						'restricted-shipping-and-payment-for-woocommerce'
					) }
					size="small"
					disabled={ isLast }
					onClick={ () => onMove( 1 ) }
				/>
				<Button
					icon="no-alt"
					label={ __(
						'Remove rule',
						'restricted-shipping-and-payment-for-woocommerce'
					) }
					size="small"
					isDestructive
					onClick={ onRemove }
				/>
			</div>
		</div>
	);
}
