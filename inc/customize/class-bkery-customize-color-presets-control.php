<?php
/**
 * カスタマイザー: テーマ同梱の配色を適用し、標準の色へ戻す。
 *
 * @package bakery-s
 */

defined( 'ABSPATH' ) || exit;

/**
 * 配色の選択と初期化ボタン。色そのものは各 bkery_color_* 設定が持つ。
 */
class Bkery_Customize_Color_Presets_Control extends WP_Customize_Control {
	/**
	 * Control type.
	 *
	 * @var string
	 */
	public $type = 'bkery_color_presets';

	/**
	 * コントロールマークアップ。
	 */
	public function render_content() {
		$input_id = '_customize-input-' . $this->id;
		?>
		<?php if ( $this->label ) : ?>
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		<?php endif; ?>
		<?php if ( $this->description ) : ?>
			<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
		<?php endif; ?>

		<div class="bkery-color-presets">
			<div class="bkery-color-presets__row">
				<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>-select">
					<?php esc_html_e( '配色を選ぶ', 'bakery-s' ); ?>
				</label>
				<select id="<?php echo esc_attr( $input_id ); ?>-select" class="bkery-color-presets__select">
					<option value=""><?php esc_html_e( '配色を選ぶ', 'bakery-s' ); ?></option>
					<?php foreach ( bkery_color_presets() as $preset_id => $preset ) : ?>
						<option value="<?php echo esc_attr( $preset_id ); ?>"><?php echo esc_html( $preset['label'] ); ?></option>
					<?php endforeach; ?>
				</select>
				<button type="button" class="button button-secondary bkery-color-presets__apply">
					<?php esc_html_e( '適用', 'bakery-s' ); ?>
				</button>
			</div>
			<button type="button" class="button button-secondary bkery-color-presets__reset">
				<?php esc_html_e( '初期の色に戻す', 'bakery-s' ); ?>
			</button>
		</div>
		<?php
	}
}
