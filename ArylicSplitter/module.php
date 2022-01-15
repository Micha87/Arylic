<?php

declare(strict_types=1);
	class ArylicSplitter extends IPSModule
	{
		public function Create()
		{
			//Never delete this line!

			$radioStationDefault = json_encode([
				['name' => 'SWR3',             'URL' => 'swr-swr3-live.cast.addradio.de/swr/swr3/live/mp3/128/stream.mp3', 'imageURL' => 'http://cdn-radiotime-logos.tunein.com/s24896q.png'],
				['name' => 'AC/DC Collection', 'URL' => 'streams.radiobob.de/bob-acdc/mp3-192/mediaplayer',                'imageURL' => 'http://cdn-radiotime-logos.tunein.com/s256712.png'],
				['name' => 'FFN',              'URL' => 'player.ffn.de/ffn.mp3',                                           'imageURL' => 'http://cdn-radiotime-logos.tunein.com/s8954q.png']
			]);
			parent::Create();
			$this->RegisterPropertyString('RadioStations', $radioStationDefault);

			if (!IPS_VariableProfileExists('Ary.Radio')) 
			{
            		IPS_CreateVariableProfile('Ary.Radio', 1);
			}
			
			

		}

		public function Destroy()
		{
			//Never delete this line!
			parent::Destroy();
		}

		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();
			$this->RadioStations();
		}

		public function RadioStations()
		{
        $radioStations = json_decode($this->ReadPropertyString('RadioStations'), true);
        $Associations = [];
        $Value = 1;
		//$this->SendDebug('RadioStations',$this->ReadPropertyString('RadioStations'), 0);
        if (IPS_VariableProfileExists('Ary.Radio')) 
			{
				IPS_DeleteVariableProfile('Ary.Radio');
            	IPS_CreateVariableProfile('Ary.Radio', 1);
			}
		
		foreach ($radioStations as $radioStation) {
            $Associations[] = [$Value++, $radioStation['name'], '', -1];
			$radio= $radioStation['name'];

			

			IPS_SetVariableProfileAssociation("Ary.Radio", ($Value-1),$radio, "", 0xFFFFFF);

            // associations only support up to 128 variables
            if ($Value === 129) {
                break;
            }
			        								}
				$this->SendDebug('RadioStations_',json_encode($Associations),0);
		}

	}