<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Calendar
 *
 * @author bruno
 */
require_once('Events.php');

class Calendar{
    
    public function createform(){
       for($i = 6;$i <= 24;$i++){
            $zero =  ($i < 10)?"0":"";
           //" . ($i < 10)?"0":"" . "
            $select .= "<option value='$zero$i:00'>$i:00</option>" . PHP_EOL;
            $select .= "<option value='$zero$i:30'>$i:30</option>" . PHP_EOL;
       }
       global $username;
       $form = <<<FORM
        <div id="dialogFormCreateEvent">
        <form id="formNewEvent">
        <input type="hidden" name="dateToday"/>
        <input type='hidden' name="owner_event" value="$username"/>
        <table>
            <tr>
                <td>Data: </td><td><span id="dateSelected"></span></td>
            </tr>
            <tr>
                <td>Título</td><td><input type="text" name="nameEvent"></td>
            </tr>
            <tr>
                <td>O dia todo</td>
                <td>
                    <input type="radio" name="allday" value="true"/>Sim
                    <input type="radio" name="allday" checked value="false"/>Não
                </td>
            </tr>
            <tr>
                <td>Início</td>
                <td>
                    <select id="selectStart">
                        $select
                    </select>
                </td>
             </tr>
             <tr>
                <td>Fim</td><td>
                    <select id="selectEnd">
                        $select
                    </select>
                </td>
             </tr>
            <tr>
                <td colspan=2>
                    Commentários </br>
                    <textarea cols=32 rows=10>
                    </textarea>
                </td>
            </tr>
            <tr>
                <td>Aviso</td>
                <td>
                    <select name="isalert">
                        <option value="1">Sim</option>
                        <option value="0">Não</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Avisar antes de:</td>
                <td>
                    <select name="minutusBefore">
                        <option value="15">15 minutos</option>
                        <option value="60">1 hora antes</option>
                        <option value="120">2 horas antes</option>
                        <option value="1440">1 dia antes</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td cols=2>
                    <input type="button" name="btnSaveEvent" value="Cadastrar"/>
                </td>
            </tr>
        </table>
        </form>
    </div>
FORM;
        global $squirrelmail_language;
        return $form;            
    }
    public static function miniCalendar($show){
        global $squirrelmail_language;
        $str = '<div class="miniCalendar"></div>';        
        
        if($squirrelmail_language == 'pt_BR'){
            $months = "monthNames: ['Janeiro','Fevereiro','Mar&ccedil;o','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro']";
            $weeks = "dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S']";
        }else{
            $months = "monthNames: ['January','February','March','April','Maj','Juni','Juli','August','September','Oktober','November','December']";
            $weeks = "dayNamesMin: ['S', 'M', 'T', 'W', 'T', 'F', 'S']";
        }        
        if($show){
            global $dsn_pear,$username;
            $events = new Events($dsn_pear);
            $daysBusy = $events->selecDaysBusy($username);            
            $str .= '<script>
            $(function() {
                id = 0;                              
            ';
            foreach($daysBusy as $dias){
                $str .= "diasBusy[id++] = '$dias';";
            }            
            $str .= '$( ".miniCalendar" ).datepicker({' . $months . ',' . $weeks  . ',                    
                    beforeShowDay: function(date) {                        
                        if($.inArray(formatDayEvent(date),diasBusy) != -1){                            
                            return [true,"selectDay"];
                        }
                        return [true];
                    },
                    onSelect: function(){
                        openDay("' . $squirrelmail_language . '");
                    }
                });
                $(".miniCalendar").show();
            });            
            </script>';
        }else{
            global $dsn_pear,$username;
            $events = new Events($dsn_pear);            
            $daysBusy = $events->selecDaysBusy($username);
            $str .= '<script>
            $(function() {
                id = 0;                                
            ';
            foreach($daysBusy as $dias){
                $str .= "diasBusy[id++] = '$dias';";
            }
            $str .= '$(".miniCalendar").datepicker({' . $months . ','
                . $weeks  . ',                    
                beforeShowDay: function(date) {                    
                    if($.inArray(formatDayEvent(date),diasBusy) != -1){                        
                        return [true,"selectDay"];
                    }
                    return [true];
                },
                onSelect: function(){
                    openDay("' . $squirrelmail_language . '");
                }
            });
        });
        </script>';
        }
        echo $str;
    }
}
