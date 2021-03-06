<head>
	<link rel="stylesheet" href="../../../css/ajouter-annonce.css">
</head>

	<article>
		<h1 class="h1-custom"><span><i class="far fa-plus-square"></i> Ajouter une annonce</span></h1>
		<section>
		<div id="section-ajouter-annonce">
			<h2>Informations générales</h2>
				<form class="pure-form pure-form-aligned formulaire" method="post" name="ajouterAnnonce" enctype="multipart/form-data">
					<div class="pure-control-group">
						<label for="title">Titre</label>
						<input type="text" id="title" placeholder="Titre de l'annonce" name="title" required/>
					</div>

					<div class="pure-control-group">
						<label for="cout-location">Coût mensuel de location</label>
						<input type="number" min="0" id="cout-location" placeholder="Coût mensuel de location" name="coutlocation" required/>
					</div>

					<div class="pure-control-group">
						<label for="cout-charges">Coût éventuel des charges</label>
						<input type="number" min="0" id="cout-charges" placeholder="Coût des charges" name="coutcharges"/>
					</div>

					<div class="pure-control-group">
						<label for="type">Type</label>
						<select id="type" name="typeselect">
						    <option value="T1">T1</option>
						    <option value="T2">T2</option>
						    <option value="T3">T3</option>
						    <option value="T4">T4</option>
						    <option value="T5">T5</option>
						    <option value="T6">T6</option>
						</select>
					</div>

					<div class="pure-control-group">
						<label for="superficie">Superficie</label>
						<input type="number" min="0" id="superficie" placeholder="Superficie du logement" name="superficie" required/>
					</div>

					<div class="pure-control-group">
						<label for="type-chauffage">Type de chauffage</label>
						<select id="type-chauffage" name="typechauffageselect" onchange="showEnergie('hidden_div', this)">
						    <option value="Collectif">Collectif</option>
						    <option value="Individuel">Individuel</option>
						</select>
					</div>

					<div class="pure-control-group" id="hidden_div">
						<label for="mode-energie">Mode d'énergie</label>
						<select id="mode-energie" name="modeenergieselect">
						    <option value="1">Fioul</option>
						    <option value="2">Électrique</option>
						    <option value="3">Gaz</option>
						</select>
					</div>

					<div class="pure-control-group">
						<label for="adresse">Localisation</label>
						<input type="text" id="adresse" placeholder="Adresse" name="adresse" style="margin-bottom:2px;" required/><br/>
						<label for=""></label>
						<input type="text" id="ville" placeholder="Ville" name="ville" style="margin-bottom:2px;" required/><br/>
						<label for=""></label>
						<input type="text" id="code-postal" placeholder="Code postal" name="codepostal" required/>
					</div>

					<div class="pure-control-group">
						<label for=""></label>
						<select id="region" name="region" required>
							<option value="">--Sélectionner une région--</option>
							<option value="Auvergne-Rhône-Alpes">Auvergne-Rhône-Alpes</option>
							<option value="Bourgogne-Franche-Comté">Bourgogne-Franche-Comté</option>
							<option value="Bretagne">Bretagne</option>
							<option value="Centre-Val de Loire">Centre-Val de Loire</option>
							<option value="Corse">Corse</option>
							<option value="Grand Est">Grand Est</option>
							<option value="Hauts-de-France">Hauts-de-France</option>
							<option value="Île-de-France">Île-de-France</option>
							<option value="Normandie">Normandie</option>
							<option value="Nouvelle-Aquitaine">Nouvelle-Aquitaine</option>
							<option value="Occitanie">Occitanie</option>
							<option value="Pays de la Loire">Pays de la Loire</option>
							<option value="Provence-Alpes-Côte d'Azur">Provence-Alpes-Côte d'Azur</option>
						</select>
					</div>
					
					<div class="pure-control-group" id="ajout-description">
						<label style="width:auto;" for="description">Description <span id="lblRemainingCount"></span></label><br/>
						<textarea id="description" name="description" rows="5" cols="40" maxlength="1100" placeholder="Description de l'annonce" onkeypress="textareaLengthCheck(this)" required></textarea>
					</div>

				<h2>Photo(s) du logement (5 max.)</h2>
					<div class="pure-control-group">
						<input type="file" name="image1" id="image1" accept="image/x-png,image/gif,image/jpeg,image/jpg" onchange="loadFile(event)" required/>
					</div>

					<div class="pure-control-group">
						<input type="file" name="image2" id="image2" accept="image/x-png,image/gif,image/jpeg,image/jpg" onchange="loadFile(event)"/>
					</div>

					<div class="pure-control-group">
						<input type="file" name="image3" id="image3" accept="image/x-png,image/gif,image/jpeg,image/jpg" onchange="loadFile(event)"/>
					</div>

					<div class="pure-control-group">
						<input type="file" name="image4" id="image4" accept="image/x-png,image/gif,image/jpeg,image/jpg" onchange="loadFile(event)"/>
					</div>

					<div class="pure-control-group">
						<input type="file" name="image5" id="image5" accept="image/x-png,image/gif,image/jpeg,image/jpg" onchange="loadFile(event)"/>
					</div>

					<p>Aperçu de la dernière photo chargée</p>
					<img id="imageoutput" src="#" style="margin-bottom:10px;"/>

					<div>
						<button type="submit" name="button" value="enregistrer" class="pure-button pure-button-primary btn-enregistrement">Enregistrer l'annonce (brouillon)</button>
						<button type="submit" name="button" value="publiée" class="pure-button pure-button-primary">Publier l'annonce</button>
					</div>
				</form>

			</div>
		</section>
		</article>
		
		<script src="../../../js/annonce.js"></script>