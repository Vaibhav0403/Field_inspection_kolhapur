<!DOCTYPE html>
< lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>आरोग्य उपकेंद्राची तपासणी सूची</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../public/css/arogya_inspection_form.css">
</head>
<div>
    <?php include("../includes/header.php")?> <?php include("../includes/sidebar.php")?> <div class="page-wrapper"> <div class="main-content"> <div class="container">
                <h2>आरोग्य उपकेंद्राची तपासणी सूची (ID: 2)</h2>
                <div class="info-display">
                    <p><strong>भेटीचा दिनांक:</strong> 11-06-2025</p>
                    <p><strong>अपेक्षित भेटीचा दिनांक:</strong> 08-04-2025</p>
                    <p><strong>प्राथमिक आरोग्य केंद्र:</strong> मा.शिरोली</p>
                    <p><strong>उपकेंद्र:</strong> बहिरेश्वर</p>
                </div>

                <form action="save_inspection_data.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="inspection_id" value="<?php echo htmlspecialchars($inspection_id); ?>">
    <input type="hidden" name="inspection_date_display" value="<?php echo $inspection_date_display; ?>">
    <input type="hidden" name="expected_inspection_date_display" value="<?php echo $expected_inspection_date_display; ?>">
    <input type="hidden" name="phc_name" value="<?php echo $phc_name; ?>">
    <input type="hidden" name="subcenter_name" value="<?php echo $subcenter_name; ?>">

                    <?php if (isset($_GET['status'])): ?>
                        <?php if ($_GET['status'] == 'success'): ?>
                            <div class="alert success">
                                Inspection data saved successfully!
                            </div>
                        <?php elseif ($_GET['status'] == 'error'): ?>
                            <div class="alert error">
                                Error saving inspection data. Please try again. <?php echo htmlspecialchars($_GET['message'] ?? ''); ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="section">
                        <h3 class="section-header">1. आरोग्य उपकेंद्र दिशादर्शक फलक, बाह्य सुशोभीकरण आणि परिसर</h3>
                        <div class="form-group">
                            <label>१. संरक्षित भिंत:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section1_q1_protected_wall" value="होय" required> होय</label>
                                <label><input type="radio" name="section1_q1_protected_wall" value="नाही"> नाही</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>२. प्रवेशद्वार:</label>
                            <div class="sub-question">
                                <label>लोखंडी दरवाजा:</label>
                                <div class="radio-group">
                                    <label><input type="radio" name="section1_q2a_iron_gate" value="होय" required> होय</label>
                                    <label><input type="radio" name="section1_q2a_iron_gate" value="नाही"> नाही</label>
                                </div>
                            </div>
                            <div class="sub-question">
                                <label>कमान व नामफलक:</label>
                                <div class="radio-group">
                                    <label><input type="radio" name="section1_q2b_arch_nameboard" value="होय" required> होय</label>
                                    <label><input type="radio" name="section1_q2b_arch_nameboard" value="नाही"> नाही</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>३. दिशादर्शक फलक:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section1_q3_direction_board" value="होय" required> होय</label>
                                <label><input type="radio" name="section1_q3_direction_board" value="नाही"> नाही</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>४. इमारतीचे प्लास्टर/रंगरंगोटी:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section1_q4_building_plaster_paint" value="होय" required> होय</label>
                                <label><input type="radio" name="section1_q4_building_plaster_paint" value="नाही"> नाही</label>
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <h3 class="section-header">2. आरोग्य उपकेंद्रात उपलब्ध असलेल्या सेवा</h3>
                        <div class="form-group">
                            <label>१. बाह्य रुग्ण विभाग (OPD):</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section2_q1_opd" value="होय" required> होय</label>
                                <label><input type="radio" name="section2_q1_opd" value="नाही"> नाही</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>२. प्रसुती २४*७:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section2_q2_delivery_24_7" value="होय" required> होय</label>
                                <label><input type="radio" name="section2_q2_delivery_24_7" value="नाही"> नाही</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>३. मोफत आरोग्य सेवा:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section2_q3_free_health_services" value="होय" required> होय</label>
                                <label><input type="radio" name="section2_q3_free_health_services" value="नाही"> नाही</label>
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <h3 class="section-header">3. अंतर्गत व बाह्य स्वच्छता</h3>
                        <div class="form-group">
                            <label>१. अंतर्गत स्वच्छता:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section3_q1_internal_cleanliness" value="होय" required> होय</label>
                                <label><input type="radio" name="section3_q1_internal_cleanliness" value="नाही"> नाही</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>२. परिसर स्वच्छता:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section3_q2_campus_cleanliness" value="होय" required> होय</label>
                                <label><input type="radio" name="section3_q2_campus_cleanliness" value="नाही"> नाही</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>३. प्रसुती गृहाची दैनंदिन स्वच्छता:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section3_q3_delivery_room_daily_clean" value="होय" required> होय</label>
                                <label><input type="radio" name="section3_q3_delivery_room_daily_clean" value="नाही"> नाही</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>४. स्वच्छता राखण्यासाठी आवश्यक साधनांची उपलब्धता:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section3_q4_cleaning_tools_availability" value="होय" required> होय</label>
                                <label><input type="radio" name="section3_q4_cleaning_tools_availability" value="नाही"> नाही</label>
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <h3 class="section-header">4. व्यवस्थापन</h3>
                        <div class="form-group">
                            <label>१. इलेक्ट्रिक फिटिंग:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section4_q1_electric_fitting" value="व्यवस्थित" required> व्यवस्थित</label>
                                <label><input type="radio" name="section4_q1_electric_fitting" value="अव्यवस्थित"> अव्यवस्थित</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>२. तडा गेलेल्या/गळणाऱ्या भिंती:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section4_q2_cracked_leaking_walls" value="होय" required> होय</label>
                                <label><input type="radio" name="section4_q2_cracked_leaking_walls" value="नाही"> नाही</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>३. दरवाजे व खिडक्यांची अवस्था:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section4_q3_doors_windows_condition" value="चांगली" required> चांगली</label>
                                <label><input type="radio" name="section4_q3_doors_windows_condition" value="खराब"> खराब</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>४. छतावरील साफसफाई:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section4_q4_ceiling_cleaning" value="केलेली" required> केलेली</label>
                                <label><input type="radio" name="section4_q4_ceiling_cleaning" value="केलेली नाही"> केलेली नाही</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>५. पाण्याच्या नळ/पाईप फिटिंगची अवस्था:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section4_q5_water_tap_pipe_condition" value="चांगली" required> चांगली</label>
                                <label><input type="radio" name="section4_q5_water_tap_pipe_condition" value="खराब"> खराब</label>
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <h3 class="section-header">5. रुग्ण तपासणी कक्ष</h3>
                        <div class="form-group">
                            <label>१. खुर्ची आणि टेबल:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section5_q1_chair_table" value="उपलब्ध आहे" required> उपलब्ध आहे</label>
                                <label><input type="radio" name="section5_q1_chair_table" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>२. रुग्ण तपासणी टेबल, स्टूल व पडदा:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section5_q2_patient_table_stool_curtain" value="उपलब्ध आहे" required> उपलब्ध आहे</label>
                                <label><input type="radio" name="section5_q2_patient_table_stool_curtain" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>३. संस्थेचा नकाशा:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section5_q3_institution_map" value="उपलब्ध आहे" required> उपलब्ध आहे</label>
                                <label><input type="radio" name="section5_q3_institution_map" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>४. आरोग्य निदर्शकांतील प्रगती दर्शविणारे चार्ट:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section5_q4_health_indicators_chart" value="उपलब्ध आहे" required> उपलब्ध आहे</label>
                                <label><input type="radio" name="section5_q4_health_indicators_chart" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>५. आरोग्य विषयक सेवा क्षेत्राचे वेळापत्रक:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section5_q5_health_services_schedule" value="उपलब्ध आहे" required> उपलब्ध आहे</label>
                                <label><input type="radio" name="section5_q5_health_services_schedule" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>६. Updated EDD & EPD चार्ट:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section5_q6_updated_edd_epd_chart" value="उपलब्ध आहे" required> उपलब्ध आहे</label>
                                <label><input type="radio" name="section5_q6_updated_edd_epd_chart" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>७. न्युमोनिया/सेप्सिस/अतिसार बाबत प्रोटोकॉल्स:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section5_q7_pneumonia_sepsis_diarrhea_protocols" value="उपलब्ध आहे" required> उपलब्ध आहे</label>
                                <label><input type="radio" name="section5_q7_pneumonia_sepsis_diarrhea_protocols" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>८. न्युमोनिया/सेप्सिस/अतिसार बाबत औषधी उपलब्धता:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section5_q8_pneumonia_sepsis_diarrhea_medicines" value="उपलब्ध आहे" required> उपलब्ध आहे</label>
                                <label><input type="radio" name="section5_q8_pneumonia_sepsis_diarrhea_medicines" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <h3 class="section-header">6. मनुष्यबळ उपलब्धता</h3>
                        <div class="form-group">
                            <label>आरोग्य सेविका (नियमित व कंत्राटी):</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section6_q1_female_health_worker" value="होय" required> होय</label>
                                <label><input type="radio" name="section6_q1_female_health_worker" value="नाही"> नाही</label>
                            </div>
                            <input type="number" name="section6_q1_female_health_worker_count" placeholder="संख्या" min="0">
                        </div>
                        <div class="form-group">
                            <label>आरोग्य सेवक:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section6_q2_male_health_worker" value="होय" required> होय</label>
                                <label><input type="radio" name="section6_q2_male_health_worker" value="नाही"> नाही</label>
                            </div>
                            <input type="number" name="section6_q2_male_health_worker_count" placeholder="संख्या" min="0">
                        </div>
                        <div class="form-group">
                            <label>पार्ट टाईम कर्मचारी:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section6_q3_part_time_staff" value="होय" required> होय</label>
                                <label><input type="radio" name="section6_q3_part_time_staff" value="नाही"> नाही</label>
                            </div>
                            <input type="number" name="section6_q3_part_time_staff_count" placeholder="संख्या" min="0">
                        </div>
                    </div>

                    <div class="section">
                        <h3 class="section-header">7. औषधी व्यवस्थापन</h3>
                        <div class="form-group">
                            <label>१. औषधांची मांडणी:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section7_q1_medicine_arrangement" value="व्यवस्थित" required> व्यवस्थित</label>
                                <label><input type="radio" name="section7_q1_medicine_arrangement" value="अव्यवस्थित"> अव्यवस्थित</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>२. साठा/उपलब्ध औषधांची नोंद वही:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section7_q2_stock_medicine_register" value="उपलब्ध" required> उपलब्ध</label>
                                <label><input type="radio" name="section7_q2_stock_medicine_register" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>३. एईएफआय किट:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section7_q3_aefi_kit" value="उपलब्ध" required> उपलब्ध</label>
                                <label><input type="radio" name="section7_q3_aefi_kit" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>४. मुदत बाह्य साठा नोंद:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section7_q4_expired_stock_register" value="उपलब्ध" required> उपलब्ध</label>
                                <label><input type="radio" name="section7_q4_expired_stock_register" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>तपासणी दरम्यान मुदतबाह्य औषधी आढळून आली काय?</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section7_q5_expired_medicine_found" value="होय" required> होय</label>
                                <label><input type="radio" name="section7_q5_expired_medicine_found" value="नाही"> नाही</label>
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <h3 class="section-header">8. साथरोग विभाग</h3>
                        <div class="form-group">
                            <label>१. लाल/पिवळे/हिरवे कार्ड वाटप (गाव निहाय व वर्ष निहाय माहिती):</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section8_q1_cards_distribution" value="उपलब्ध आहे" required> उपलब्ध आहे</label>
                                <label><input type="radio" name="section8_q1_cards_distribution" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>२. लाल व पिवळे कार्ड रूपांतर (माहिती):</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section8_q2_card_conversion_info" value="उपलब्ध आहे" required> उपलब्ध आहे</label>
                                <label><input type="radio" name="section8_q2_card_conversion_info" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                            </div>
                        </div>
                        <div class="sub-question">
                            <div class="form-group">
                                <label>२.१) केलेली कार्यवाही (तपशील):</label>
                                <textarea name="section8_q2_1_action_taken" rows="2"></textarea>
                            </div>
                            <div class="form-group">
                                <label>२.२) पत्रव्यवहार (तपशील):</label>
                                <textarea name="section8_q2_2_correspondence" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>३. वर्ष निहाय साथरोग उद्रेक माहिती (लागण व मृत्यू) - मागील ५ वर्षे:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section8_q3_outbreak_info_5yrs" value="उपलब्ध आहे" required> उपलब्ध आहे</label>
                                <label><input type="radio" name="section8_q3_outbreak_info_5yrs" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>४. गॅस्ट्रो, कॉलरा, कावीळ, डेंग्यू, चिकुनगुनिया बाबत गाव/वर्ष निहाय माहिती:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section8_q4_disease_village_year_info" value="उपलब्ध आहे" required> उपलब्ध आहे</label>
                                <label><input type="radio" name="section8_q4_disease_village_year_info" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>५. पाणी स्रोता बाबत माहिती (सद्यस्थिती):</label>
                            <div class="sub-question">
                                <label>५.१) स्रोत निहाय (आड, विहीर, बोअरवेल, नळयोजना) अद्ययावत (चालू/बंद) माहिती:</label>
                                <div class="radio-group">
                                    <label><input type="radio" name="section8_q5_1_water_source_info" value="उपलब्ध आहे" required> उपलब्ध आहे</label>
                                    <label><input type="radio" name="section8_q5_1_water_source_info" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                                </div>
                            </div>
                            <div class="sub-question">
                                <label>५.२) पाणी शुद्धीकरणाबाबत रजिस्टर, दूषित पाणी पुरवठा पत्रव्यवहार:</label>
                                <div class="radio-group">
                                    <label><input type="radio" name="section8_q5_2_water_purification_register_correspondence" value="उपलब्ध आहे" required> उपलब्ध आहे</label>
                                    <label><input type="radio" name="section8_q5_2_water_purification_register_correspondence" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                                </div>
                            </div>
                            <div class="sub-question">
                                <label>५.३) पाणी नमुने पाठवण्याचे रजिस्टर (उपकेंद्र व गाव स्तर):</label>
                                <div class="radio-group">
                                    <label><input type="radio" name="section8_q5_3_water_sample_register" value="उपलब्ध आहे" required> उपलब्ध आहे</label>
                                    <label><input type="radio" name="section8_q5_3_water_sample_register" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                                </div>
                            </div>
                            <div class="sub-question">
                                <label>५.४) मुख्यालयातील पाणी नमुने पाठवलेबाबतचे रेकॉर्ड:</label>
                                <div class="radio-group">
                                    <label><input type="radio" name="section8_q5_4_hq_water_sample_record" value="उपलब्ध आहे" required> उपलब्ध आहे</label>
                                    <label><input type="radio" name="section8_q5_4_hq_water_sample_record" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>६. पाणी पुरवठा टँकर बाबत:</label>
                            <div class="sub-question">
                                <label>६.१) टंचाईग्रस्त गावे, टँकर पुरवठा, शुद्धीकरण रेकॉर्ड (माहे एप्रिल २०१७ पासून):</label>
                                <div class="radio-group">
                                    <label><input type="radio" name="section8_q6_1_water_tanker_record" value="उपलब्ध आहे" required> उपलब्ध आहे</label>
                                    <label><input type="radio" name="section8_q6_1_water_tanker_record" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                                </div>
                            </div>
                            <div class="sub-question">
                                <label>६.२) गाव निहाय टी.सी.एल. उपलब्धता:</label>
                                <div class="radio-group">
                                    <label><input type="radio" name="section8_q6_2_village_tcl_availability" value="उपलब्ध आहे" required> उपलब्ध आहे</label>
                                    <label><input type="radio" name="section8_q6_2_village_tcl_availability" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                                </div>
                            </div>
                            <div class="sub-question">
                                <label>६.३) खरेदी रेकॉर्ड (टी.सी.एल. इत्यादी):</label>
                                <div class="radio-group">
                                    <label><input type="radio" name="section8_q6_3_purchase_record_tcl" value="उपलब्ध आहे" required> उपलब्ध आहे</label>
                                    <label><input type="radio" name="section8_q6_3_purchase_record_tcl" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                                </div>
                            </div>
                            <div class="sub-question">
                                <label>६.४) नमुना पाठवण्याचे रेकॉर्ड (टँकर संबंधित):</label>
                                <div class="radio-group">
                                    <label><input type="radio" name="section8_q6_4_sample_record_tanker" value="उपलब्ध आहे" required> उपलब्ध आहे</label>
                                    <label><input type="radio" name="section8_q6_4_sample_record_tanker" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                                </div>
                            </div>
                            <div class="sub-question">
                                <label>६.५) २०% पेक्षा कमी क्लोरीन असलेली गावे (माहिती):</label>
                                <div class="radio-group">
                                    <label><input type="radio" name="section8_q6_5_low_chlorine_villages" value="उपलब्ध आहे" required> उपलब्ध आहे</label>
                                    <label><input type="radio" name="section8_q6_5_low_chlorine_villages" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>७. दंश माहिती:</label>
                            <div class="sub-question">
                                <label>७.१) श्वान दंश, सर्प दंश, विंचू व इतर दंश अहवाल:</label>
                                <div class="radio-group">
                                    <label><input type="radio" name="section8_q7_1_bite_reports" value="उपलब्ध आहे" required> उपलब्ध आहे</label>
                                    <label><input type="radio" name="section8_q7_1_bite_reports" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>८. इतर नोंदी:</label>
                            <div class="sub-question">
                                <label>८.१) मीठ नमुना, शौच नमुना बाबत रेकॉर्ड:</label>
                                <div class="radio-group">
                                    <label><input type="radio" name="section8_q8_1_salt_stool_sample_record" value="उपलब्ध आहे" required> उपलब्ध आहे</label>
                                    <label><input type="radio" name="section8_q8_1_salt_stool_sample_record" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                                </div>
                            </div>
                            <div class="sub-question">
                                <label>८.२) ग्राम आरोग्य, पाणी पुरवठा व स्वच्छता समिती बैठक रेकॉर्ड:</label>
                                <div class="radio-group">
                                    <label><input type="radio" name="section8_q8_2_gram_health_water_sanitation_meeting_record" value="उपलब्ध आहे" required> उपलब्ध आहे</label>
                                    <label><input type="radio" name="section8_q8_2_gram_health_water_sanitation_meeting_record" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                                </div>
                            </div>
                            <div class="sub-question">
                                <label>८.३) शुद्धीकरणाबाबत असलेले औषधी उपलब्धता:</label>
                                <div class="radio-group">
                                    <label><input type="radio" name="section8_q8_3_purification_medicine_availability" value="उपलब्ध आहे" required> उपलब्ध आहे</label>
                                    <label><input type="radio" name="section8_q8_3_purification_medicine_availability" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                                </div>
                            </div>
                            <div class="sub-question">
                                <label>८.४) साथरोग किट व झिंक व ओ.आर.टी कॉर्नर:</label>
                                <div class="radio-group">
                                    <label><input type="radio" name="section8_q8_4_epidemic_kit_zinc_ort_corner" value="उपलब्ध आहे" required> उपलब्ध आहे</label>
                                    <label><input type="radio" name="section8_q8_4_epidemic_kit_zinc_ort_corner" value="नाही"> नाही</label>
                                </div>
                            </div>
                            <div class="sub-question">
                                <label>८.५) पाणी शुद्धीकरण बाबत प्रशिक्षण इतिवृत्त:</label>
                                <div class="radio-group">
                                    <label><input type="radio" name="section8_q8_5_water_purification_training_minutes" value="उपलब्ध आहे" required> उपलब्ध आहे</label>
                                    <label><input type="radio" name="section8_q8_5_water_purification_training_minutes" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>९. इतर निरीक्षणे (डीएलएस कडील):</label>
                            <textarea name="section8_q9_other_observations_dls" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="section">
                        <h3 class="section-header">9. आरोग्य उपकेंद्र रेकॉर्ड/रजिस्टर</h3>
                        <div class="form-group">
                            <label>१. OPD रजिस्टर:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="section9_q1_opd_register" value="उपलब्ध आहे" required> उपलब्ध आहे</label>
                                <label><input type="radio" name="section9_q1_opd_register" value="उपलब्ध नाही"> उपलब्ध नाही</label>
                            </div>
                        </div>
                          
            </div>
              <button class="btn-submit"type="submit">सबमिट</button> <button class="btn-reset" type="reset">रीसेट करा</button>
                        </form>
        </div>
       
    </div> 
    </div>
        

    <script>
        // JavaScript for handling conditional visibility of count fields and GPS capture
        document.addEventListener('DOMContentLoaded', function() {
            // Function to toggle number input based on radio selection
            function setupConditionalNumberInput(radioGroupName, numberInputName) {
                const radios = document.querySelectorAll(`input[name="${radioGroupName}"]`);
                const numberInput = document.querySelector(`input[name="${numberInputName}"]`);

                radios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        if (this.value === 'होय') {
                            numberInput.style.display = 'block';
                            numberInput.setAttribute('required', 'required');
                        } else {
                            numberInput.style.display = 'none';
                            numberInput.removeAttribute('required');
                            numberInput.value = ''; // Clear value if not applicable
                        }
                    });
                });

                // Initialize state on page load
                const initialSelectedRadio = document.querySelector(`input[name="${radioGroupName}"]:checked`);
                if (initialSelectedRadio && initialSelectedRadio.value === 'होय') {
                    numberInput.style.display = 'block';
                    numberInput.setAttribute('required', 'required');
                } else {
                    numberInput.style.display = 'none';
                    numberInput.removeAttribute('required');
                }
            }

            setupConditionalNumberInput('section6_q1_female_health_worker', 'section6_q1_female_health_worker_count');
            setupConditionalNumberInput('section6_q2_male_health_worker', 'section6_q2_male_health_worker_count');
            setupConditionalNumberInput('section6_q3_part_time_staff', 'section6_q3_part_time_staff_count');


            // GPS Capture Logic (client-side)
            const photoInputs = [
                { fileInput: document.querySelector('input[name="live_photo_1"]'), latInput: document.getElementById('photo_1_latitude'), lonInput: document.getElementById('photo_1_longitude') },
                { fileInput: document.querySelector('input[name="live_photo_2"]'), latInput: document.getElementById('photo_2_latitude'), lonInput: document.getElementById('photo_2_longitude') },
                { fileInput: document.querySelector('input[name="live_photo_3"]'), latInput: document.getElementById('photo_3_latitude'), lonInput: document.getElementById('photo_3_longitude') },
                { fileInput: document.querySelector('input[name="live_photo_4"]'), latInput: document.getElementById('photo_4_latitude'), lonInput: document.getElementById('photo_4_longitude') }
            ];

            photoInputs.forEach(item => {
                item.fileInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        if (navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition(
                                function(position) {
                                    item.latInput.value = position.coords.latitude;
                                    item.lonInput.value = position.coords.longitude;
                                    console.log(`GPS for ${item.fileInput.name}: Lat ${position.coords.latitude}, Lon ${position.coords.longitude}`);
                                },
                                function(error) {
                                    console.warn(`ERROR(${error.code}): ${error.message}`);
                                    alert('GPS लोकेशन मिळवता आले नाही. कृपया लोकेशन चालू असल्याची खात्री करा आणि पुन्हा प्रयत्न करा.');
                                    item.latInput.value = '';
                                    item.lonInput.value = '';
                                },
                                { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
                            );
                        } else {
                            alert('आपल्या ब्राउझरमध्ये GPS/लोकेशन सेवा उपलब्ध नाही.');
                        }
                    } else {
                        item.latInput.value = '';
                        item.lonInput.value = '';
                    }
                });
            });
        });
    </script>
</body>
 <?php include("../includes/footer.php")?>
</html>