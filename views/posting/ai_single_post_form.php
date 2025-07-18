<div class="modal fade" id="exampleModal1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
   
   <div id="loadingImage" style="display:none ;" class="overlay">
   
   <!-- <img src="'.home_url('/').'wp-content/plugins/jobseq_jobs_pugin/assets/image/loader.gif" alt="Loading..."> -->
   </div>
   <div id="loadingAIData" style="display:none;" class="overlay_ai_data"></div>
   
   <div id="loadingAIImage" style="display:none;" class="overlay_ai_image"></div>
   
           <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
              <div class="modal-content">
                  <div class="modal-header">
                      <h5 class="modal-title" id="exampleModalLabel">Generate AI Content</h5>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close" id= "butn"><span aria-hidden="true">&times;</span></button>
                  </div>
				  <form id="popup_form" method="post" class="pop_up_form">
                   <div class="modal-body">
                       <div id="smartwizard">
                           <ul style="margin: 0px 30px 5px 30px;">
                               <li style="width: 18%;">
                                   <a href="#step-1" style="text-align: center;">
                                       Step 1<br />
                                       <small>Keyword Input</small>
                                   </a>
                               </li>
                               <li style="width: 18%;">
                                   <a href="#step-2" style="text-align: center;">
                                       Step 2<br />
                                       <small>Content Setting</small>
                                   </a>
                               </li>
                               <li style="width: 18%;">
                                   <a href="#step-3" style="text-align: center;">
                                       Step 3<br />
                                       <small>Add Media</small>
                                   </a>
                               </li>
                               <li style="width: 20%;">
                                   <a href="#step-4" style="text-align: center;">
                                       Step 4<br />
   									<small>Generate AI Content</small>
                                   </a>
                               </li>
   							<li style="width: 20%;">
                                   <a href="#step-5" style="text-align: center;">
                                       Step 5<br />
   									<small>Meta Title & Description</small>
                                   </a>
                               </li>
                           </ul>
                           <div>
                           	
                               <div id="step-1">
                                  <div class="row">
                                   <div class="form-group col-md-1"></div>
                                       <div class="seedform-group col-md-11 desc" id="seed">
                                        	<textarea class="form-control" style="width: 84%; resize:none;" placeholder="Enter Seed Keyword" id="seed_keyword" name="seed_keyword"></textarea>
   											<span id="error_seed_keyword" style="color: red;"></span>
                                               <select id="seed_select" name="seed_options" class="form-control" style="max-width: 84% !important; margin-top: 15px;">
                                                       <option value="">Select Title Type</option>
                                                       <option value="seed_option1">USE KEYWORD AS IS IN TITLE [A.I. will build content]</option>
                                                       <option value="seed_option2">CREATE BEST TITLE FROM KEYWORD [A.I. will choose/build content]</option>
                                                       <option value="seed_option3">CREATE BEST QUESTION FROM KEYWORD [A.I. will choose/build content]</option>  
                                               </select>
   											<span id="error_seed_select" style="color: red;"></span>
                                               <div style="clear: both"> </div>
                                               <div class="content_type">
   											   <div class="form-group col-md-11 " id="seed" style="padding: 0px"> 
   											   <select class="form-control" name="content_type" id="cotnt_type" style="max-width: 92% !important;">
   													   <option value="">Tone of Voice</option>
   													   <option value="friendly">Friendly</option>
   													   <option value="professional">Professional</option>
   													   <option value="informational">Informational</option>
   													   <option value="transactional">Transactional</option>
   													   <option value="inspirational">Inspirational</option>
   													   <option value="neutral">Neutral</option>
   													   <option value="witty">Witty</option>
   													   <option value="casual">Casual</option>
   													   <option value="authoritative">Authoritative</option>
   													   <option value="encouraging">Encouraging</option>
   													   <option value="persuasive">Persuasive</option>
   													   <option value="poetic">Poetic</option>
   											   </select>
   											   <span id="error_cotnt_type" style="color: red;"></span>
   													<div class="form-group col-md-1"></div>
   												</div>
                                               </div>
                                               <div style="clear: both"> </div>
                                               <div id="loader" style="display: none;">Loading...</div>
                                               <div style="clear: both"> </div>
                                               <label id="gettitle"><span><input type='checkbox' id='checkbox_need' /></span><span id="maintitle"> </span><label id="reload"><i class="fa fa-refresh" aria-hidden="true"></i></label></label>
                                               <input type="hidden" name="aigeneratedtitle" id="aigeneratedtitle" />
   
   											<span id="errorContainer" style="color: red;"></span> 
                                       </div>
                                    </div>
   
   									<div class="row">
   										<div class="form-group col-md-1"></div>
   										
   										</div>
                               		</div>
   
                               <div id="step-2">
                                   <div class="row">
                                      
   									<div class="form-group col-md-12">
   									<label for="sel1">Article size</label>
   									<select class="form-control" name="nos_of_words" required  style="max-width: 100% !important;" id="post_size">
   										
   										<option value="600 to 1200 words">Small </option>
   										<option value="1200 to 2400 words">Medium </option>
   										<option value="2400 to 3600 words">Large</option>
   									
   								   </select>
   								</div>
                                   </div>
   								<div class="row">
                                      
   									<div class="form-group col-md-12">
   									
   									<input type="text" id="post_size_select" readonly style="width: 100% !important;" value="600-1200 words">
   									
   								</div>
                                   </div>
   								<div class="row">
   								<div class="form-group col-md-6">
   								<label for="sel1">Point of View</label>
   								<select class="form-control" name="point_of_view" >
   									
   									<option value="none">None
   									</option>
   									<option value="First person singular (I,me,my,mine)">First person singular (I,me,my,mine)
   									</option>
   									<option value="First person plural (we,us,our,ours)">First person plural (we,us,our,ours)</option>
   									<option value="Second Person (you,your,yours)">Second Person (you,your,yours)</option>
   									
   								
   							   </select>
   							</div>
   								<div class="form-group col-md-6">
   								<label for="language">Select Language</label>
   								<select class="form-control" name="content_lang" id="language">
   									<option value="">-Select Language-</option>
   									<option value="english_us">English (US)</option>
   									<option value="english_uk">English (Uk)</option>
   									
   								  </select>
   							</div>
   							</div>
                                   <div class="row">
										<div class="form-group col-md-12">
											<label for="sel1">Details to Include <a href="#" data-toggle="Please ensure the information you input aligns with the Main Keyword and Title. For example, information about dogs should not be added if you are writing about roofing." title="Please ensure the information you input aligns with the Main Keyword and Title. For example, information about dogs should not be added if you are writing about roofing."><div class="dashicons dashicons-info-outline" aria-hidden="true"><br></div></a></label>
											<textarea class="form-control" id="exampleFormControlTextarea1" rows="3" name="details_to_include" onkeypress="return countContent()" OnBlur="LimitText(this,500,1)"></textarea>
											<span id="countContent"></span>
										</div>
                                   </div>
   
   								<div class="row">
   								<div class="form-group col-md-12">
   									<label for="sel1">Call to action <a href="#" data-toggle="Information" title="Information"><div class="dashicons dashicons-info-outline" aria-hidden="true"><br></div></a></label>
   									<textarea class="form-control" id="call_to_action" rows="3" name="call_to_action" onkeypress="return countContentCallToAction()" OnBlur="LimitText(this,500,2)"></textarea><span id="countContentCallToAction"></span>	
   								</div>
                                      
                                   </div>
                               </div>
   
                               <div id="step-3" class="">
                                   <div class="row" style="padding-left: 50px; padding-right: 50px">
   									<div class="col-md-6" style="text-align: left;margin-top: 30px;">
   										<input type="radio" name="aiImage" value="AI_image" id="AI_image">
   <label>Generate AI Image Based On Title</label> 
   									</div>
   
   									<div class="col-md-6" style="text-align: left;margin-top: 30px;">
   										<input type="radio" name="aiImage"onclick="SeedShow()" value="Manually_image" id="Manually_image">
   <label>Manually Upload Image</label> 
   									</div>
   
   									<div class="col-md-6" style="text-align: left;margin-top: 30px;margin-bottom: 30px;">
   										<input type="radio" name="aiImage" value="manually_promt_image" id="manually_promt_image"> <label>Generate AI Image - Edit Prompt</label> 
   									</div>
								      
   									<div id="AI_image_div" style="display:none; margin: 0 0 0 33%;">
   										<div id="ai-image-display"></div>
   										<div class="form-group col-md-12" style="margin: 0 0 0 40%;" id="AIrefreshOption" >
   											<i class="fa fa-refresh" aria-hidden="true" onclick="return refreshAIImage()" style="cursor:pointer;"></i>
   										</div>
   										<input type="hidden" id="AI-Image-uploaded-path" name="AI-Image-uploaded-path">
   									</div>
   
   									<div id="manually_image_div" style="display:none; margin: 0 0 0 33%;" >
   										<input type="file" id="upload-image-button" name="Manually_image">
   										<div id="manually-image-display"></div>
   										<input type="hidden" id="manually-image-uploaded-path" name="manually-image-uploaded-path">
   									</div>
   
   									<div id="prompt_image_div" style="display:none; margin: 0 0 0 33%;">
   										<div id="ai-with-prompt-image-display"></div>
   										
   										<input type="hidden" id="AI-Prompt-Image-uploaded-path" name="AI-Prompt-Image-uploaded-path">
   									</div>
									
   									<div class="form-group col-md-12" id="Prompt_to_create_Dalle_Image" style="margin: 0 0 0 0; display: none;">

   										<div id="manually_promt" style="margin: 0px 40px 0px 40px;">
   											<textarea class="form-control" id="manually_promt_for_image" rows="3" name="manually_promt_for_image" onkeypress="return countContent()" OnBlur="LimitText(this,500,3)"></textarea>
   											<span id="error_manually_promt_for_image"></span>
   											<input type="button" name="generate_i_image" class="btn btn-primary pull-right"  id="generate_i_image" value="Generate Image" style="margin: 10px 0px 0px 0px;" />
   										</div>
   									</div>
   									</div>
   									
                               </div>
   
                               <div id="step-4" class="">
   								<div class="row">
   									<div class="col-md-12" style="text-align: left; margin-top: 30px; margin-bottom: 30px;">
   									
   									<textarea class="form-control" id="showmydataindivText"  rows="1" style="opacity: 0;"></textarea>
   									
   									<div id="showmydataindiv1" name="showmydataindiv1" style="display: block;max-width: 100%;overflow-y: scroll;"></div>
   									<input type="hidden" name="ai_tittle" id="ai_title" />
   										<div style="text-align: center; display: flex; justify-content: center; gap: 10px; margin: 20px;">
   											<input type="button" value="Approve Content" class="btn btn-primary" onclick="return saveData()" id="generateapi" style="display:none;" style="margin: 0px 0px -37px 0px;">
   											<input type="button" name="genaipost" class="btn btn-primary" id="generateapivalue" value="Generate AI Post" />
   											<input type="hidden" name="AI_Title" id="AI_Title">
   											<input type="hidden" name="AI_descreption" id="AI_descreption">
   										</div>
   									</div>
   								</div>
                               </div>
   
   							<div id="step-5" class="">
   								<div class="row">
   									<div class="col-md-12" style="text-align: center; margin-top: 30px; margin-bottom: 30px;">
   									<div class="form-group col-md-12">
   										<label for="sel1">Meta Title:</label>
   										<input type="text" class="form-control" id="meta_title" name="meta_title">
   									</div>
   
   									<div class="form-group col-md-12">
   										<label for="sel1">Meta Description</label>
   										<textarea class="form-control" id="meta_descreption" rows="3" name="meta_descreption"></textarea>
   									</div>
   									<input type="button" value="Submit" onclick="return saveFinalData()">
   
   									</div>
   								</div>
                               </div>
   
                           </div>
                       </div>
                   </div>
                </form>
              </div>
          </div>
      </div>