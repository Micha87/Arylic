<?php

declare(strict_types=1);
	class Arylic extends IPSModule
	{
		public function Create()
		{
			//Never delete this line!
			parent::Create();
			$this->createVariablenProfiles();
			$this->RegisterPropertyString('IPAddresse', 'xxx.xxx.xxx.xxx');
           	//$this->RegisterPropertyInteger('TimeOut', 1000);
			
			
			#Variablen Anlegen
			$this->RegisterVariableString("DeviceName", "Device Name","",1);
			$this->RegisterVariableInteger("InputSource", "Source","Ary.Input",2);
			$this->EnableAction('InputSource');
			$this->RegisterVariableInteger("Volume", "Volume","~Intensity.100",3);
			$this->EnableAction('Volume');
			$this->RegisterVariableBoolean("Mute", "Mute","Ary.Mute",4);
			$this->EnableAction('Mute');
			$this->RegisterVariableString("Titel", "Titel","",5);
			$this->RegisterVariableString("Artist", "Artist","",6);
			$this->RegisterVariableString("Album", "Album","",7);
			$this->RegisterVariableString("Mode", "Mode","",8);
			$this->RegisterVariableInteger("Control", "Control","Ary.Control",9);
			$this->EnableAction('Control');
			$this->RegisterVariableInteger("Preset", "Preset","Ary.Presets",9);
			$this->EnableAction('Preset');
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
		}
	
	
		public function GetDeviceInformation()
    	{
			$IP_Adresse=$this->ReadPropertyString('IPAddresse');
			//$this->SendDebug('Ip-Adresse', $this->ReadPropertyString('IPAddresse'), 0);

			$Json=Sys_GetURLContent("http://$IP_Adresse/httpapi.asp?command=getStatusEx");
			$this->SendDebug('GetDeviceInformation',$Json, 0);
			
			$json_decode = json_decode($Json);
			//Zonenname
			//echo ($json->DeviceName);
			SetValue($this->GetIDForIdent('DeviceName'),$json_decode->DeviceName);
		}

		public function GetPlayerState()
		{
			$IP_Adresse=$this->ReadPropertyString('IPAddresse');
			$Json=Sys_GetURLContent("http://$IP_Adresse/httpapi.asp?command=getPlayerStatus");
			$this->SendDebug('GetPlayerState',$Json, 0);
			
			$json_decode = json_decode($Json);
			
			//Play
			if((($json_decode->status) == "play") or (($json_decode->status) == "load"))
				{
					SetValue($this->GetIDForIdent('Control'),1);
				}
			else
				{
					SetValue($this->GetIDForIdent('Control'),3);
				}
			
			SetValue($this->GetIDForIdent('Volume'),$json_decode->vol);
			SetValue($this->GetIDForIdent('Mute'),$json_decode->mute);
						
			if(($json_decode->Title)=='unknown')
			{
				SetValue($this->GetIDForIdent('Titel'),"");
				$this->SendDebug('Titel',$json_decode->Title, 0);
			}
			else
			{
				SetValue($this->GetIDForIdent('Titel'),$this->hexToStr($json_decode->Title));
			}
			
			
			if(($json_decode->Artist)=='unknown')
			{
				SetValue($this->GetIDForIdent('Artist'),"");
				$this->SendDebug('Artist',$json_decode->Artist, 0);
			}
			else
			{
				SetValue($this->GetIDForIdent('Artist'),$this->hexToStr($json_decode->Artist));
			}
			
			if(($json_decode->Album)=='unknown')
			{
				SetValue($this->GetIDForIdent('Album'),"");
				$this->SendDebug('Album',$json_decode->Album, 0);
			}
			else
			{
				SetValue($this->GetIDForIdent('Album'),$this->hexToStr($json_decode->Album));
			}
			
			$this->SendDebug('Mode',$json_decode->mode, 0);

			switch($json_decode->mode){
				case '0':
					SetValue($this->GetIDForIdent('Mode'),"Idling");
				break;
				case '1':
					SetValue($this->GetIDForIdent('Mode'),"airplay streaming");
				break;
				case '2':
					SetValue($this->GetIDForIdent('Mode'),"DLNA streaming");
				break;
				case '10':
					SetValue($this->GetIDForIdent('Mode'),"Playing network content");
				break;
				case '11':
					SetValue($this->GetIDForIdent('Mode'),"playing UDISK");
					SetValue($this->GetIDForIdent('InputSource'),"5");
				break;
				case '20':
					SetValue($this->GetIDForIdent('Mode'),"playback start by HTTPAPI");
				break;
				case '31':
					SetValue($this->GetIDForIdent('Mode'),"Spotify Connect streaming");
				break;
				case '40':
					SetValue($this->GetIDForIdent('Mode'),"Line-In input mode");
					SetValue($this->GetIDForIdent('InputSource'),"1");
				break;
				case '41':
					SetValue($this->GetIDForIdent('Mode'),"Bluetooth input mode");
				break;
				case '43':
					SetValue($this->GetIDForIdent('Mode'),"Optical input mode");
					SetValue($this->GetIDForIdent('InputSource'),"2");
				break;
				case '47':
					SetValue($this->GetIDForIdent('Mode'),"Line-In2 input mode");
					SetValue($this->GetIDForIdent('InputSource'),"4");
				break;
				case '51':
					SetValue($this->GetIDForIdent('Mode'),"SBDAC input mode");
				break;
				case '99':
					SetValue($this->GetIDForIdent('Mode'),"The Device is a Guest in a Multiroom Zone");
				break;
				}
			}

		private function SetInputSource($Value)
    	{
			$IP_Adresse=$this->ReadPropertyString('IPAddresse');
						
			switch($Value){
				case '0':
					$return=Sys_GetURLContent("http://$IP_Adresse/httpapi.asp?command=setPlayerCmd:switchmode:wifi");
					$this->SendDebug('playmode', "wifi", 0);
					if($return=="OK")
					{
						SetValue($this->GetIDForIdent('InputSource'),"0");
					}
					break;
				case '1':
					Sys_GetURLContent("http://$IP_Adresse/httpapi.asp?command=setPlayerCmd:switchmode:line-in");
					$this->SendDebug('playmode', "line-in", 0);
					break;		
				case '2':
					Sys_GetURLContent("http://$IP_Adresse/httpapi.asp?command=setPlayerCmd:switchmode:optical");
					$this->SendDebug('playmode', "optical", 0);
					break;
				case '3':
					$return=Sys_GetURLContent("http://$IP_Adresse/httpapi.asp?command=setPlayerCmd:switchmode:co-axial");
					$this->SendDebug('playmode', "co-axial", 0);
					if($return=="OK")
					{
						SetValue($this->GetIDForIdent('InputSource'),"3");
					}
					break;		
				case '4':
					Sys_GetURLContent("http://$IP_Adresse/httpapi.asp?command=setPlayerCmd:switchmode:line-in2");
					$this->SendDebug('playmode', "line-in2", 0);
					break;
				case '5':
					Sys_GetURLContent("http://$IP_Adresse/httpapi.asp?command=setPlayerCmd:switchmode:udisk");
					break;		
					$this->SendDebug('playmode', "udisk", 0);
				}
					
		}
		
		private function SetVolume($Value)
		{
			$IP_Adresse=$this->ReadPropertyString('IPAddresse');
				Sys_GetURLContent("http://$IP_Adresse/httpapi.asp?command=setPlayerCmd:vol:$Value");
				$this->SendDebug('Volume', $Value, 0);
				
		}

		private function SetMute($Value)
		{
			$IP_Adresse=$this->ReadPropertyString('IPAddresse');
				Sys_GetURLContent("http://$IP_Adresse/httpapi.asp?command=setPlayerCmd:mute:$Value");
				$this->SendDebug('Mute', $Value, 0);
				
		}
		private function SetControl($Value)
		{
			$IP_Adresse=$this->ReadPropertyString('IPAddresse');
			$this->SendDebug('Control', $Value, 0);
			
			switch($Value){
				case '0':
					Sys_GetURLContent("http://$IP_Adresse/httpapi.asp?command=setPlayerCmd:pause");
					break;
				case '1':
					Sys_GetURLContent("http://$IP_Adresse/httpapi.asp?command=setPlayerCmd:resume");
					break;
				case '2':
					Sys_GetURLContent("http://$IP_Adresse/httpapi.asp?command=setPlayerCmd:onepause");
					break;
				case '3':
					Sys_GetURLContent("http://$IP_Adresse/httpapi.asp?command=setPlayerCmd:stop");
					break;
				case '4':
					Sys_GetURLContent("http://$IP_Adresse/httpapi.asp?command=setPlayerCmd:prev");
					break;
				case '5':
					Sys_GetURLContent("http://$IP_Adresse/httpapi.asp?command=setPlayerCmd:next");
					break;
				}


		}
		private function SetPresets($Value)
		{
			$IP_Adresse=$this->ReadPropertyString('IPAddresse');
			$this->SendDebug('Preset', $Value, 0);
			
			$return=Sys_GetURLContent("http://$IP_Adresse/httpapi.asp?command=MCUKeyShortClick:$Value");
			if($return=="OK"){
				SetValue($this->GetIDForIdent('Preset'),$Value);
			}
		}
		private function hexToStr($hex)
		{
			$string='';
			for ($i=0; $i < strlen($hex)-1; $i+=2){
				$string .= chr(hexdec($hex[$i].$hex[$i+1]));
			}
			return $string;
		 }


		public function RequestAction($Ident, $Value)
		{
			switch ($Ident) {
				case'InputSource':
					$this->SetInputSource($Value);
					$this->GetPlayerState();
					break;
				case'Volume':
					$this->SetVolume($Value);
					$this->GetPlayerState();
					break;	
				case'Mute':
					$this->SetMute($Value);
					$this->GetPlayerState();
					break;
				case'Control':
					$this->SetControl($Value);
					$this->GetPlayerState();
					break;
				case'Preset':
					$this->SetPresets($Value);
					$this->GetPlayerState();
					break;
			}
		}
		private function createVariablenProfiles()
   		{
        	if (!IPS_VariableProfileExists('Ary.Input')) 
			{
            		IPS_CreateVariableProfile('Ary.Input', 1);
			}
			
			IPS_SetVariableProfileAssociation("Ary.Input", 0, "wifi", "", 0xFFFFFF);
			IPS_SetVariableProfileAssociation("Ary.Input", 1, "line-in", "", 0xFFFFFF);
			IPS_SetVariableProfileAssociation("Ary.Input", 2, "optical", "", 0xFFFFFF);
			IPS_SetVariableProfileAssociation("Ary.Input", 3, "co-axial", "", 0xFFFFFF);
			IPS_SetVariableProfileAssociation("Ary.Input", 4, "line-in2", "", 0xFFFFFF);
			IPS_SetVariableProfileAssociation("Ary.Input", 5, "udisk", "", 0xFFFFFF);	
			
			if (!IPS_VariableProfileExists('Ary.Mute')) 
			{
            		IPS_CreateVariableProfile('Ary.Mute', 0);
			}
			
			IPS_SetVariableProfileAssociation("Ary.Mute", 0, "Not Muted", "", 0xFFFFFF);
			IPS_SetVariableProfileAssociation("Ary.Mute", 1, "Mute", "", 0xFFFFFF);

			if (!IPS_VariableProfileExists('Ary.Control')) 
			{
            		IPS_CreateVariableProfile('Ary.Control', 1);
			}
			
			IPS_SetVariableProfileAssociation("Ary.Control", 0, "Pause", "", 0xFFFFFF);
			IPS_SetVariableProfileAssociation("Ary.Control", 1, "Play", "", 0xFFFFFF);
			IPS_SetVariableProfileAssociation("Ary.Control", 2, "Toggle", "", 0xFFFFFF);
			IPS_SetVariableProfileAssociation("Ary.Control", 3, "Stop", "", 0xFFFFFF);
			IPS_SetVariableProfileAssociation("Ary.Control", 4, "<<", "", 0xFFFFFF);
			IPS_SetVariableProfileAssociation("Ary.Control", 5, ">>", "", 0xFFFFFF);
			
			if (!IPS_VariableProfileExists('Ary.Presets')) 
			{
            		IPS_CreateVariableProfile('Ary.Presets', 1);
			}
			
			IPS_SetVariableProfileAssociation("Ary.Presets", 0, "", "", 0xFFFFFF);
			IPS_SetVariableProfileAssociation("Ary.Presets", 1, "", "", 0xFFFFFF);
			IPS_SetVariableProfileAssociation("Ary.Presets", 2, "", "", 0xFFFFFF);
			IPS_SetVariableProfileAssociation("Ary.Presets", 3, "", "", 0xFFFFFF);
			IPS_SetVariableProfileAssociation("Ary.Presets", 4, "", "", 0xFFFFFF);
			IPS_SetVariableProfileAssociation("Ary.Presets", 5, "", "", 0xFFFFFF);
		}	
	}
