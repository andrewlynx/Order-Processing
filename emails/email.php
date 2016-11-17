<?php
/**
 * Order Processing email template
 *
 * @version  1.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bg              = get_option( 'woocommerce_email_background_color' );
$body            = get_option( 'woocommerce_email_body_background_color' );
$base            = get_option( 'woocommerce_email_base_color' );
$base_text       = wc_light_or_dark( $base, '#202020', '#ffffff' );
$text            = get_option( 'woocommerce_email_text_color' );
?>
<div id="wrapper" dir="ltr" style="background-color:<?php echo $bg; ?>;margin:0;padding:70px 0 70px 0;width:100%">
	<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
		<tbody>
			<tr>
				<td align="center" valign="top">
					<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container" style="background-color:<?php echo $body; ?>;border:1px solid #dcdcdc;border-radius:3px!important">
						<tbody>
							<tr>
								<td align="center" valign="top">
									<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_header" style="background-color:<?php echo $base; ?>;border-radius:3px 3px 0 0!important;color:#202020;border-bottom:0;font-weight:bold;line-height:100%;vertical-align:middle;font-family:&quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif;width:640px">
										<tbody>
											<tr>
												<td id="header_wrapper" style="padding:36px 48px;display:block">
													<h1 style="color:#202020;font-family:&quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif;font-size:30px;font-weight:300;line-height:150%;margin:0;text-align:left"><?php echo $email_heading; ?></h1>
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
							<tr>
								<td align="center" valign="top">
									<table border="0" cellpadding="20" cellspacing="0" width="100%">
										<tbody>
											<tr>
												<td valign="top" style="padding:25px">
													<div id="body_content_inner" style="color:<?php echo $text; ?>;font-family:&quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif;font-size:14px;line-height:150%;text-align:left">
														<?php echo $email_content; ?>
													</div>
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
							<tr>
								<td align="center" valign="top">
									<table border="0" cellpadding="10" cellspacing="0" width="600" id="template_footer">
										<tbody>
											<tr>
												<td valign="top" style="padding:0">
													<table border="0" cellpadding="10" cellspacing="0" width="100%">
														<tbody>
															<tr>
																<td colspan="2" valign="middle" id="credit" style="padding:0 48px 48px 48px;border:0;color:#c2c8cd;font-family:Arial;font-size:12px;line-height:125%;text-align:center">
																	<?php echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ); ?>
																</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>
	</table>
</div>