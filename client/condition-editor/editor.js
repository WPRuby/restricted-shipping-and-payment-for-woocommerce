/**
 * WordPress dependencies
 */
import { Button, Notice } from '@wordpress/components';
import { useEffect, useMemo, useRef, useState } from '@wordpress/element';
import { __, _n, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import RuleRow, { newRule } from './rule-row';
import TokenSelect from './token-select';
import { validate } from './validation';

let lastKey = 0;
const nextKey = () => `k${ ++lastKey }`;

/**
 * Give every rule a React key; new conditions start with one rule.
 *
 * @param {Object[]} rules     Rules from PHP.
 * @param {Object[]} ruleTypes
 * @return {Object[]} Rules with keys.
 */
function withKeys( rules, ruleTypes ) {
	return ( rules.length ? rules : [ newRule( ruleTypes[ 0 ] ) ] ).map(
		( rule ) => ( { ...rule, _key: nextKey() } )
	);
}

/**
 * The condition editor.
 *
 * @param {Object} props
 * @param {Object} props.data window.rspwLiteConditionEditor, see ConditionEditor::get_editor_data().
 */
export default function ConditionEditor( { data } ) {
	const {
		condition,
		ruleTypes,
		targets: targetOptions,
		selectedLabels,
		currency,
		fieldName,
	} = data;
	const isPayment = condition.context === 'payment';

	const [ targets, setTargets ] = useState( condition.targets );
	const [ rules, setRules ] = useState( () =>
		withKeys( condition.rules, ruleTypes )
	);
	const [ showErrors, setShowErrors ] = useState( false );
	const rootRef = useRef();

	const errors = useMemo(
		() =>
			validate(
				{ context: condition.context, targets, rules },
				ruleTypes
			),
		[ condition.context, targets, rules, ruleTypes ]
	);
	const errorCount = useRef( 0 );
	errorCount.current = errors.count;

	// Keep the hidden field that is saved with the post up to date.
	useEffect( () => {
		const input = document.getElementById( fieldName );
		if ( input ) {
			input.value = JSON.stringify( {
				rules: rules.map( ( { _key, ...rule } ) => rule ),
				targets,
			} );
		}
	}, [ fieldName, rules, targets ] );

	// Block saving while the condition is invalid. Drafts can be saved anyway.
	useEffect( () => {
		const form = document.getElementById( 'post' );
		if ( ! form ) {
			return undefined;
		}
		const onSubmit = ( event ) => {
			const submitter = event.submitter;
			if ( submitter && submitter.id === 'save-post' ) {
				return;
			}
			if ( errorCount.current > 0 ) {
				event.preventDefault();
				setShowErrors( true );
				rootRef.current?.scrollIntoView( {
					behavior: 'smooth',
					block: 'start',
				} );
				// WordPress marks the buttons busy when they are clicked; make them usable again.
				window.setTimeout( () => {
					form.querySelectorAll(
						'#submitpost .button.disabled'
					).forEach( ( button ) =>
						button.classList.remove( 'disabled' )
					);
					form.querySelectorAll(
						'#submitpost .spinner.is-active'
					).forEach( ( spinner ) =>
						spinner.classList.remove( 'is-active' )
					);
				} );
			}
		};
		form.addEventListener( 'submit', onSubmit );
		return () => form.removeEventListener( 'submit', onSubmit );
	}, [] );

	const shown = showErrors
		? errors
		: { targets: '', rules: '', ruleErrors: [], count: 0 };

	const updateRule = ( i, rule ) =>
		setRules( rules.map( ( r, j ) => ( j === i ? rule : r ) ) );

	const moveRule = ( i, direction ) => {
		const next = [ ...rules ];
		const [ rule ] = next.splice( i, 1 );
		next.splice( i + direction, 0, rule );
		setRules( next );
	};

	return (
		<div className="rspw-editor" ref={ rootRef }>
			{ shown.count > 0 && (
				<Notice status="error" isDismissible={ false }>
					{ sprintf(
						/* translators: %d: number of problems. */
						_n(
							'Fix %d problem below before publishing this condition.',
							'Fix %d problems below before publishing this condition.',
							shown.count,
							'restricted-shipping-and-payment-for-woocommerce'
						),
						shown.count
					) }
				</Notice>
			) }

			<section className="rspw-editor__section">
				<h3>
					{ isPayment
						? __(
								'Hide these payment gateways',
								'restricted-shipping-and-payment-for-woocommerce'
						  )
						: __(
								'Hide these shipping methods',
								'restricted-shipping-and-payment-for-woocommerce'
						  ) }
				</h3>
				<TokenSelect
					label={
						isPayment
							? __(
									'Payment gateways',
									'restricted-shipping-and-payment-for-woocommerce'
							  )
							: __(
									'Shipping methods',
									'restricted-shipping-and-payment-for-woocommerce'
							  )
					}
					value={ targets }
					options={ targetOptions }
					onChange={ setTargets }
					help={
						shown.targets ? (
							<span className="rspw-field-error">
								{ shown.targets }
							</span>
						) : undefined
					}
				/>
			</section>

			<section className="rspw-editor__section">
				<h3>
					{ __(
						'When the cart matches any of these rules',
						'restricted-shipping-and-payment-for-woocommerce'
					) }
				</h3>

				<div className="rspw-rules">
					{ rules.map( ( rule, i ) => (
						<div key={ rule._key }>
							{ i > 0 && (
								<div className="rspw-rules__joiner">
									{ __(
										'or',
										'restricted-shipping-and-payment-for-woocommerce'
									) }
								</div>
							) }
							<RuleRow
								rule={ rule }
								ruleTypes={ ruleTypes }
								errors={ shown.ruleErrors[ i ] }
								knownLabels={ selectedLabels }
								currency={ currency }
								isFirst={ i === 0 }
								isLast={ i === rules.length - 1 }
								onChange={ ( next ) => updateRule( i, next ) }
								onRemove={ () =>
									setRules(
										rules.filter( ( r, j ) => j !== i )
									)
								}
								onMove={ ( direction ) =>
									moveRule( i, direction )
								}
							/>
						</div>
					) ) }
				</div>

				{ shown.rules && (
					<p className="rspw-field-error">{ shown.rules }</p>
				) }

				<Button
					variant="secondary"
					icon="plus-alt2"
					onClick={ () =>
						setRules( [
							...rules,
							{ ...newRule( ruleTypes[ 0 ] ), _key: nextKey() },
						] )
					}
				>
					{ __(
						'Add rule',
						'restricted-shipping-and-payment-for-woocommerce'
					) }
				</Button>
			</section>
		</div>
	);
}
