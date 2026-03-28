<?php
/**
 * Multi-step form handler for WPRobo DocuMerge.
 *
 * Provides utilities for grouping fields into steps, validating
 * individual steps, and evaluating conditional visibility logic.
 *
 * @package   WPRobo_DocuMerge
 * @since     1.0.0
 */

namespace WPRobo\DocuMerge\Form;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPRobo_DocuMerge_Multi_Step
 *
 * Handles multi-step form logic including step grouping, per-step
 * validation, and conditional field evaluation.
 *
 * @since 1.0.0
 */
class WPRobo_DocuMerge_Multi_Step {

	/**
	 * Groups fields by their step number.
	 *
	 * Each field config is expected to have a 'step' property indicating
	 * which step it belongs to. Fields without a step default to step 1.
	 *
	 * @since 1.0.0
	 *
	 * @param array $fields Array of field configuration objects.
	 * @return array Associative array keyed by step number, each containing
	 *               an array of field configs belonging to that step.
	 */
	public function wprobo_documerge_get_steps( $fields ) {
		$steps = array();

		foreach ( $fields as $field ) {
			$step_number = isset( $field['step'] ) ? absint( $field['step'] ) : 1;

			if ( 0 === $step_number ) {
				$step_number = 1;
			}

			if ( ! isset( $steps[ $step_number ] ) ) {
				$steps[ $step_number ] = array();
			}

			$steps[ $step_number ][] = $field;
		}

		ksort( $steps );

		return $steps;
	}

	/**
	 * Returns the total number of steps based on field configurations.
	 *
	 * Determines the highest step number present in the fields array.
	 *
	 * @since 1.0.0
	 *
	 * @param array $fields Array of field configuration objects.
	 * @return int The total number of steps (highest step number).
	 */
	public function wprobo_documerge_get_step_count( $fields ) {
		$max_step = 1;

		foreach ( $fields as $field ) {
			$step_number = isset( $field['step'] ) ? absint( $field['step'] ) : 1;

			if ( $step_number > $max_step ) {
				$max_step = $step_number;
			}
		}

		return $max_step;
	}

	/**
	 * Validates all fields within a given step.
	 *
	 * Iterates over each field in the step, skips fields hidden by
	 * conditional logic, and collects any validation errors.
	 *
	 * @since 1.0.0
	 *
	 * @param array                               $step_fields    Array of field configurations for the step.
	 * @param array                               $post_data      The submitted form data ($_POST).
	 * @param WPRobo_DocuMerge_Field_Registry     $field_registry The field registry instance.
	 * @return true|array True if all fields pass validation, or an array
	 *                    of \WP_Error objects for each failing field.
	 */
	public function wprobo_documerge_validate_step( $step_fields, $post_data, $field_registry ) {
		$errors = array();

		foreach ( $step_fields as $field_data ) {
			// Skip fields hidden by conditional logic.
			if ( ! empty( $field_data['conditions'] ) && is_array( $field_data['conditions'] ) ) {
				$should_show = $this->wprobo_documerge_evaluate_conditions( $field_data['conditions'], $post_data );

				if ( ! $should_show ) {
					continue;
				}
			}

			$type           = isset( $field_data['type'] ) ? $field_data['type'] : '';
			$field_instance = $field_registry->wprobo_documerge_get_field( $type );

			if ( null === $field_instance ) {
				continue;
			}

			$name  = isset( $field_data['name'] ) ? $field_data['name'] : '';
			$value = isset( $post_data[ $name ] ) ? $post_data[ $name ] : '';

			// Sanitize before validating.
			$value  = $field_instance->wprobo_documerge_sanitize( $value, $field_data );
			$result = $field_instance->wprobo_documerge_validate( $value, $field_data );

			if ( is_wp_error( $result ) ) {
				$errors[] = $result;
			}
		}

		if ( ! empty( $errors ) ) {
			return $errors;
		}

		return true;
	}

	/**
	 * Determines whether the given step is the last step.
	 *
	 * @since 1.0.0
	 *
	 * @param int $step        The current step number.
	 * @param int $total_steps The total number of steps.
	 * @return bool True if the current step is the last step.
	 */
	public function wprobo_documerge_is_last_step( $step, $total_steps ) {
		return (int) $step >= (int) $total_steps;
	}

	/**
	 * Evaluates conditional visibility rules to determine if a field should be shown.
	 *
	 * Each condition is an associative array with keys:
	 * - 'field'    (string) The name of the field to check against.
	 * - 'operator' (string) One of: equals, not_equals, contains, not_contains,
	 *                       is_empty, is_not_empty.
	 * - 'value'    (string) The value to compare against.
	 * - 'action'   (string) Either 'show' or 'hide'.
	 *
	 * All conditions are evaluated. If any condition with action 'hide' matches,
	 * the field is hidden. If any condition with action 'show' matches, the field
	 * is shown. If no conditions match, the field defaults to shown.
	 *
	 * @since 1.0.0
	 *
	 * @param array $conditions Array of condition arrays.
	 * @param array $form_data  The submitted form data to check conditions against.
	 * @return bool True if the field should be shown, false if hidden.
	 */
	public function wprobo_documerge_evaluate_conditions( $conditions, $form_data ) {
		if ( empty( $conditions ) || ! is_array( $conditions ) ) {
			return true;
		}

		foreach ( $conditions as $condition ) {
			$field_name      = isset( $condition['field'] ) ? $condition['field'] : '';
			$operator        = isset( $condition['operator'] ) ? $condition['operator'] : 'equals';
			$condition_value = isset( $condition['value'] ) ? $condition['value'] : '';
			$action          = isset( $condition['action'] ) ? $condition['action'] : 'show';

			// Look up field value — also check array-style keys for checkboxes.
			if ( isset( $form_data[ $field_name ] ) ) {
				$field_value = $form_data[ $field_name ];
			} elseif ( isset( $form_data[ $field_name . '[]' ] ) ) {
				$field_value = $form_data[ $field_name . '[]' ];
			} else {
				$field_value = '';
			}

			// If the field value is an array, convert to comma-separated string.
			if ( is_array( $field_value ) ) {
				$field_value = implode( ',', $field_value );
			}

			$match = false;

			switch ( $operator ) {
				case 'equals':
					$match = ( (string) $field_value === (string) $condition_value );
					break;

				case 'not_equals':
					$match = ( (string) $field_value !== (string) $condition_value );
					break;

				case 'contains':
					$match = ( '' !== $condition_value && false !== strpos( (string) $field_value, (string) $condition_value ) );
					break;

				case 'not_contains':
					$match = ( '' === $condition_value || false === strpos( (string) $field_value, (string) $condition_value ) );
					break;

				case 'is_empty':
					$match = ( '' === trim( (string) $field_value ) );
					break;

				case 'is_not_empty':
					$match = ( '' !== trim( (string) $field_value ) );
					break;
			}

			if ( 'hide' === $action ) {
				// "Hide when condition matches" → hide if matched, show if not.
				if ( $match ) {
					return false;
				}
			}

			if ( 'show' === $action ) {
				// "Show when condition matches" → show if matched, HIDE if not.
				if ( $match ) {
					return true;
				} else {
					return false;
				}
			}
		}

		// Default: show the field if no conditions defined.
		return true;
	}
}
