<?php
class PHPCalendar {
		
	private $weekDayName = array ("LUNE","MART","MIER","JUEV","VIER","SABA","DOMI");
        
	private $currentDay = 0;
	private $currentMonth = 0;
	private $currentYear = 0;
	private $currentMonthStart = null;
	private $currentMonthDaysLength = null;	
            
	function __construct() {
		$this->currentYear = date ( "Y", time () );
		$this->currentMonth = date ( "m", time () );
		
		if (! empty ( $_POST ['year'] )) {
			$this->currentYear = $_POST ['year'];
		}
		if (! empty ( $_POST ['month'] )) {
			$this->currentMonth = $_POST ['month'];
		}
		$this->currentMonthStart = $this->currentYear . '-' . $this->currentMonth . '-01';
		$this->currentMonthDaysLength = date ( 't', strtotime ( $this->currentMonthStart ) );
                
                
                
	}
	
        public function getPorcentajeAudi($db,$anio,$mes,$id_auditor){
            // get dias ocupados por el auditor
            $sql="SELECT 
                SUM(porcentaje) AS porcentaje,
                sum(case when id_actividad IN (17,18,21,20,23,19) then 1 else 0 end) as tipo,
                DAY(fecha) AS dia
                FROM prg_auditoractividad
                WHERE id_auditor='$id_auditor' AND MONTH(fecha)=$mes AND YEAR(fecha)=$anio
                AND flag='1' AND IFNULL(usuario_ingreso,'')!='feriado' 
                GROUP BY DAY(fecha)";

				$consulta=$db->consultar($sql);		
				if(!empty($consulta)){
					foreach($consulta as $row){
						if($row['tipo']>0) $data[$row['dia']]="<font color=green>".$row['porcentaje']."</font>";
						else $data[$row['dia']]=$row['porcentaje'];
					}
				}
				return $data;
        }
		
		public function getFeriado($db,$anio,$mes,$id_auditor){
            // get dias ocupados por el auditor
            $sql="SELECT DAY(fecha) AS dia, prg_feriado.descripcion
                FROM prg_feriado inner join prg_auditor on  prg_feriado.id_pais=prg_auditor.id_pais
                WHERE id_auditor='$id_auditor' AND MONTH(fecha)=$mes AND YEAR(fecha)=$anio
                AND prg_feriado.flag='1' 
				and id_feriado not in (select id_feriado from prg_feriadoxauditor where id_auditor=$id_auditor)";

				$consulta=$db->consultar($sql);		
				if(!empty($consulta)){
					foreach($consulta as $row){
						$data[$row['dia']]="<font color=red>".$row['descripcion']."</font>";
						
					}
				}
				return $data;
        }
        
	function getCalendarHTML($db,$id_auditor,$weekDayName) {
        if(!empty($id_auditor)){   
			$calendarHTML = '<div id="calendar-outer">'; 
			$calendarHTML .= '<div class="calendar-nav">' . $this->getCalendarNavigation() . '</div>'; 
			$calendarHTML .= '<ul class="week-name-title">' . $this->getWeekDayName ($weekDayName) . '</ul>';
			$calendarHTML .= '<ul class="week-day-cell">' . $this->getWeekDays ($db,$id_auditor) . '</ul>';		
			$calendarHTML .= '</div>';
			return $calendarHTML;
		} else return "Error";
	}
	
	function getCalendarNavigation() {
		$prevMonthYear = date ( 'm,Y', strtotime ( $this->currentMonthStart. ' -1 Month'  ) );
		$prevMonthYearArray = explode(",",$prevMonthYear);
		
		$nextMonthYear = date ( 'm,Y', strtotime ( $this->currentMonthStart . ' +1 Month'  ) );
		$nextMonthYearArray = explode(",",$nextMonthYear);
		
		$navigationHTML = '<div class="prev" data-prev-month="' . $prevMonthYearArray[0] . '" data-prev-year = "' . $prevMonthYearArray[1]. '"><</div>'; 
		$navigationHTML .= '<span id="currentMonth">' . date ( 'M', strtotime ( $this->currentMonthStart ) ) . '</span>';
		$navigationHTML .= '<span contenteditable="true" id="currentYear" style="margin-left:5px">'.	date ( 'Y', strtotime ( $this->currentMonthStart ) ) . '</span>';
		$navigationHTML .= '<div class="next" data-next-month="' . $nextMonthYearArray[0] . '" data-next-year = "' . $nextMonthYearArray[1]. '">></div>';
		return $navigationHTML;
	}
	
	function getWeekDayName($weekDayName) {
		$WeekDayName= '';		
		foreach ( $weekDayName as $dayname ) {			
			$WeekDayName.= '<li>' . $dayname . '</li>';
		}		
		return $WeekDayName;
	}
	
	function getWeekDays($db,$id_auditor) {

                // añade valores de dias
                 $mes=$this->currentMonth;
                 $anio=$this->currentYear;
                 $data=$this->getPorcentajeAudi($db,$anio,$mes,$id_auditor);
				 $dataFer=$this->getFeriado($db,$anio,$mes,$id_auditor);
               
                // fin valores
            
		$weekLength = $this->getWeekLengthByMonth ();
		$firstDayOfTheWeek = date ( 'N', strtotime ( $this->currentMonthStart ) );
		$weekDays = "";
		for($i = 0; $i < $weekLength; $i ++) {
			$weekDays.= "<br>";
			for($j = 1; $j <= 7; $j ++) {
				$cellIndex = $i * 7 + $j;
				$cellValue = null;
				if ($cellIndex == $firstDayOfTheWeek) {
					$this->currentDay = 1;
				}
				if (! empty ( $this->currentDay ) && $this->currentDay <= $this->currentMonthDaysLength) {
					$cellValue = $this->currentDay;
					$this->currentDay ++;
				}
                                $fecha=$anio."-".$mes."-".$cellValue;
                                if(!empty($dataFer[$cellValue])) 
									$txtFeriado="<font color=red>".$cellValue."(100%)</font>";
								else
									$txtFeriado=$cellValue;
									
								if(!empty($data[$cellValue])) 
                                    $weekDays .= '<li><a href=javascript:js_detalleCalendar('.$id_auditor.',"'.$fecha.'")>' . $txtFeriado .'</a> (' . $data[$cellValue].  '%) </li>';
                                else 
                                    $weekDays .= '<li><a href=javascript:js_detalleCalendar('.$id_auditor.',"'.$fecha.'")>' . $txtFeriado . '</a> </li>';
			}
		}
		return $weekDays;
	}
	
	function getWeekLengthByMonth() {
		$weekLength =  intval ( $this->currentMonthDaysLength / 7 );	
		if($this->currentMonthDaysLength % 7 > 0) {
			$weekLength++;
		}
		$monthStartDay= date ( 'N', strtotime ( $this->currentMonthStart) );		
		$monthEndingDay= date ( 'N', strtotime ( $this->currentYear . '-' . $this->currentMonth . '-' . $this->currentMonthDaysLength) );
		if ($monthEndingDay < $monthStartDay) {			
			$weekLength++;
		}
		
		return $weekLength;
	}
}
?>