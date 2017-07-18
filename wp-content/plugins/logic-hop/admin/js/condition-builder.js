function logicHopConditionBuilder () {
	
	var self = this;
	this.condition_json;
	this.condition_count = 0;
	this.condition_logic = 'and';
	this.condition_class = 'form-inline';
	this.condition_input_class = 'form-control';
	this.info_default = {};
	this.operators = {};
	this.operators_greater_less = {};
	this.conditions = {};
	
	this.setData = function () {
	
		self.info_default = logichop_text.info_default;
		
		self.operators = {
			"==": logichop_text.equal, 
			">": logichop_text.greater, 
			"<=": logichop_text.less_equal,  
			">=": logichop_text.greater_equal, 
			"<": logichop_text.less, 
			"!=": logichop_text.not_equal	
		};
	
		self.operators_greater_less = {
			">": logichop_text.greater,
			"<": logichop_text.less
		};
	
		self.conditions = {
			"first_visit": {
				"label": logichop_text.first,
				"map": '{"#operator": [ {"var": "FirstVisit" }, true ] }',
				"lookup": "\"FirstVisit\"",
				"type": "default",
				"inputs": [
					{
						"label": "Operator",
						"name": "operator",
						"type": "select",
						"options": {
							"==": logichop_text.first_true, 
							"!=": logichop_text.first_false
						}
					}
				],
				"info": logichop_text.first_info
			},
			"direct_visit": {
				"label": logichop_text.direct,
				"map": '{"#operator": [ {"var": "Source" }, "direct" ] }',
				"lookup": "Source",
				"type": "default",
				"inputs": [
					{
						"label": "Operator",
						"name": "operator",
						"type": "select",
						"options": {
							"==": logichop_text.direct_true, 
							"!=": logichop_text.direct_false
						}
					}
				],
				"info": logichop_text.direct_info
			},
			"lead_score": {
				"label": logichop_text.lead_score,
				"map": '{"#operator": [ {"var": "LeadScore" }, "#value" ] }',
				"lookup": "LeadScore",
				"type": "default",
				"inputs": [
					{
						"label": "Operator",
						"name": "operator",
						"type": "select",
						"options": self.operators
					},
					{
						"label": "Value",
						"name": "value",
						"type": "number",
						"placeholder": logichop_text.value,
						"map": 1
					}
				],
				"info": logichop_text.lead_score_info
			},
			"geo": {
				"label": logichop_text.geo,
				"map": '{"#operator": [ {"var": "Location.#var" }, "#value" ] }',
				"lookup": "Location\\.",
				"type": "valueSubStrIndex",
				"inputs": [
					{
						"label": "Location Type",
						"name": "var",
						"type": "select",
						"options": {
							"CountryCode": "Country Code (US, CA, etc)",
							"RegionCode": "State/Region Code (CA, NY, etc)",
							"City": "City",
							"ZIPCode": "ZIP Code",
							"MetroCode": "Metro Code"
						},
						"map": 0
					},
					{
						"label": "Operator",
						"name": "operator",
						"type": "select",
						"options": {
							"==": logichop_text.is, 
							"!=": logichop_text.is_not,
							"in": logichop_text.is_in_list
						}
					},
					{
						"label": "Value",
						"name": "value",
						"type": "text",
						"placeholder": logichop_text.value,
						"map": 1
					}
				],
				"info": logichop_text.geo_info
			},
			"IP": {
				"label": logichop_text.ip,
				"map": '{"#operator": [ {"var": "IP" }, "#value" ] }',
				"lookup": "IP",
				"type": "default",
				"inputs": [
					{
						"label": "Operator",
						"name": "operator",
						"type": "select",
						"options": {
							"==": logichop_text.is, 
							"!=": logichop_text.is_not,
							"in": logichop_text.is_in_list
						}
					},
					{
						"label": "Value",
						"name": "value",
						"type": "text",
						"placeholder": logichop_text.value,
						"map": 1
					}
				],
				"info": logichop_text.ip_info
			},
			"mobile": {
				"label": logichop_text.mobile,
				"map": '{"#operator": [ {"var": "Mobile" }, true ] }',
				"lookup": "Mobile",
				"type": "default",
				"inputs": [
					{
						"label": "Operator",
						"name": "operator",
						"type": "select",
						"options": {
							"==": logichop_text.mobile_true, 
							"!=": logichop_text.mobile_false
						}
					}
				],
				"info": logichop_text.mobile_info
			},
			"tablet": {
				"label": logichop_text.tablet,
				"map": '{"#operator": [ {"var": "Tablet" }, true ] }',
				"lookup": "Tablet",
				"type": "default",
				"inputs": [
					{
						"label": "Operator",
						"name": "operator",
						"type": "select",
						"options": {
							"==": logichop_text.tablet_true, 
							"!=": logichop_text.tablet_false
						}
					}
				],
				"info": logichop_text.tablet_info
			},
			"time_elapsed": {
				"label": logichop_text.elapsed,
				"map": '{"#operator": [{"-":[{"var": "Date.Timestamp"},{"var": "Timestamp.#time"}]}, #elapsed]}',
				"lookup": "Timestamp.",
				"type": "default",
				"inputs": [
					{
						"label": "Time Since",
						"name": "time",
						"type": "select",
						"options": {
							"LastPage": logichop_text.elapsed_3,
							"ThisVisit": logichop_text.elapsed_4,
							"LastVisit": logichop_text.elapsed_2, 
							"FirstVisit": logichop_text.elapsed_1
						},
						"map": 0
					},
					{
						"label": "Operator",
						"name": "operator",
						"type": "select",
						"options": self.operators_greater_less
					},
					{
						"label": "Time Elapsed",
						"name": "elapsed",
						"type": "select",
						"options": {
							"15": "Fifteen Seconds",
							"30": "Thirty Seconds",
							"60": "One Minute",
							"300": "Five Minutes",
							"600": "Ten Minutes",
							"900": "Fifteen Minutes",
							"1800": "Thirty Minutes",
							"3600": "One Hour",
							"7200": "Two Hours",
							"10800": "Three Hours",
							"14400": "Four Hours",
							"18000": "Five Hours",
							"21600": "Six Hours",
							"43200": "Twelve Hours",
							"86400": "One Day",
							"172800": "Two Days",
							"259200": "Three Days",
							"345600": "Four Days",
							"432000": "Five Days",
							"518400": "Six Days",
							"604800": "One Week",
							"1209600": "Two Weeks",
							"1814400": "Three Weeks",
							"2419200": "One Month",
							"4838400": "Two Months",
							"7257600": "Three Months"
						},
						"map": 1
					}
				],
				"info": logichop_text.elapsed_info
			},
			"goal_state": {
				"label": logichop_text.goal,
				"map": '{"#operator": [ {"key_exists": [#goal, {"var": "Goals" }] }, true] }',
				"lookup": "Goals\"",
				"type": "valueKeyExists",
				"inputs": [
					{
						"label": "Goal Name",
						"name": "goal",
						"type": "select",
						"options": logichop_goals,
						"map": 0
					},
					{
						"label": "Operator",
						"name": "operator",
						"type": "select",
						"options": {
							"==": logichop_text.completed, 
							"!=": logichop_text.not_completed
						}
					}
				],
				"info": logichop_text.goal_info
			},
			"goal_state_s": {
				"label": logichop_text.goal_s,
				"map": '{"#operator": [ {"key_exists": [#goal, {"var": "GoalsSession" }] }, true] }',
				"lookup": "GoalsSession\"",
				"type": "valueKeyExists",
				"inputs": [
					{
						"label": "Goal Name",
						"name": "goal",
						"type": "select",
						"options": logichop_goals,
						"map": 0
					},
					{
						"label": "Operator",
						"name": "operator",
						"type": "select",
						"options": {
							"==": logichop_text.completed, 
							"!=": logichop_text.not_completed
						}
					}
				],
				"info": logichop_text.goal_info_s
			},
			"goal_specific_views": {
				"label": logichop_text.goal_cnt,
				"map": '{"#operator": [ {"var": "Goals.#goal" }, #views ] }',
				"lookup": "Goals\\.",
				"type": "valueSubStrIndex",
				"inputs": [
					{
						"label": "Goal",
						"name": "goal",
						"type": "select",
						"options": logichop_goals,
						"map": 0
					},
					{
						"label": "Operator",
						"name": "operator",
						"type": "select",
						"options": self.operators
					},
					{
						"label": "Views",
						"name": "views",
						"type": "number",
						"map": 1
					}
				],
				"info": logichop_text.goal_cnt_info
			},
			"goal_specific_views_s": {
				"label": logichop_text.goal_cnt_s,
				"map": '{"#operator": [ {"var": "GoalsSession.#goal" }, #views ] }',
				"lookup": "GoalsSession\\.",
				"type": "valueSubStrIndex",
				"inputs": [
					{
						"label": "Goal",
						"name": "goal",
						"type": "select",
						"options": logichop_goals,
						"map": 0
					},
					{
						"label": "Operator",
						"name": "operator",
						"type": "select",
						"options": self.operators
					},
					{
						"label": "Views",
						"name": "views",
						"type": "number",
						"map": 1
					}
				],
				"info": logichop_text.goal_cnt_info_s
			},
			"page_current_views": {
				"label": logichop_text.current,
				"map": '{"#operator": [ {"var": "Views" }, #views ] }',
				"lookup": "Views\"",
				"type": "default",
				"inputs": [
					{
						"label": "Operator",
						"name": "operator",
						"type": "select",
						"options": self.operators
					},
					{
						"label": "Views",
						"name": "views",
						"map": 1,
						"type": "number"
					}
				],
				"info": logichop_text.current_info
			},
			"page_current_views_s": {
				"label": logichop_text.current_s,
				"map": '{"#operator": [ {"var": "ViewsSession" }, #views ] }',
				"lookup": "ViewsSession",
				"type": "default",
				"inputs": [
					{
						"label": "Operator",
						"name": "operator",
						"type": "select",
						"options": self.operators
					},
					{
						"label": "Views",
						"name": "views",
						"map": 1,
						"type": "number"
					}
				],
				"info": logichop_text.current_info_s
			},
			"pages_total_views": {
				"label": logichop_text.total,
				"map": '{"#operator": [ {"add_array":{"var":"Pages"}}, #views ] }',
				"lookup": "add_array\":{",
				"type": "default",
				"inputs": [
					{
						"label": "Operator",
						"name": "operator",
						"type": "select",
						"options": self.operators
					},
					{
						"label": "Views",
						"name": "views",
						"map": 1,
						"type": "number"
					}
				],
				"info": logichop_text.total_info
			},
			"pages_total_views_s": {
				"label": logichop_text.total_s,
				"map": '{"#operator": [ {"add_array":{"var":"PagesSession"}}, #views ] }',
				"lookup": 'add_array\":{\"var\":\"PagesSession',
				"type": "default",
				"inputs": [
					{
						"label": "Operator",
						"name": "operator",
						"type": "select",
						"options": self.operators
					},
					{
						"label": "Views",
						"name": "views",
						"map": 1,
						"type": "number"
					}
				],
				"info": logichop_text.total_info_s
			},
			"page_specific_views": {
				"label": logichop_text.specific,
				"map": '{"#operator": [ {"var": "Pages.#page" }, #views ] }',
				"lookup": "Pages\\.",
				"type": "valueSubStrIndex",
				"inputs": [
					{
						"label": "Page",
						"name": "page",
						"type": "select",
						"options": logichop_pages,
						"map": 0
					},
					{
						"label": "Operator",
						"name": "operator",
						"type": "select",
						"options": self.operators
					},
					{
						"label": "Views",
						"name": "views",
						"type": "number",
						"map": 1
					}
				],
				"info": logichop_text.specific_info
			},
			"page_specific_views_s": {
				"label": logichop_text.specific_s,
				"map": '{"#operator": [ {"var": "PagesSession.#page" }, #views ] }',
				"lookup": "PagesSession\\.",
				"type": "valueSubStrIndex",
				"inputs": [
					{
						"label": "Page",
						"name": "page",
						"type": "select",
						"options": logichop_pages,
						"map": 0
					},
					{
						"label": "Operator",
						"name": "operator",
						"type": "select",
						"options": self.operators
					},
					{
						"label": "Views",
						"name": "views",
						"type": "number",
						"map": 1
					}
				],
				"info": logichop_text.specific_info_s
			},
			"referrer": {
				"label": logichop_text.referrer,
				"map": '{"#operator": ["#url", {"var": "Referrer" }]}',
				"lookup": "Referrer",
				"type": "default",
				"inputs": [
					{
						"label": "Operator",
						"name": "operator",
						"type": "select",
						"options": {
							"==": logichop_text.is, 
							"!=": logichop_text.is_not, 
							"in": logichop_text.contains
						}
					},
					{
						"label": logichop_text.url,
						"name": "url",
						"type": "text",
						"map": 0
					}
				],
				"info": logichop_text.referrer_info
			},
			"query": {
				"label": logichop_text.query,
				"map": '{"#operator": [ {"var": "Query.#var" }, "#value" ] }',
				"lookup": "Query\\.",
				"type": "valueSubStrIndex",
				"inputs": [
					{
						"label": "Variable",
						"name": "var",
						"type": "text",
						"placeholder": logichop_text.variable,
						"map": 0
					},
					{
						"label": "Operator",
						"name": "operator",
						"type": "select",
						"options": {
							"==": logichop_text.is, 
							"!=": logichop_text.is_not
						}
					},
					{
						"label": "Value",
						"name": "value",
						"type": "text",
						"placeholder": logichop_text.value,
						"map": 1
					}
				],
				"info": logichop_text.query_info
			},		
			"query_stored": {
				"label": logichop_text.query_se,
				"map": '{"#operator": [ {"var": "QueryStore.#var" }, "#value" ] }',
				"lookup": "QueryStore",
				"type": "valueSubStrIndex",
				"inputs": [
					{
						"label": "Variable",
						"name": "var",
						"type": "text",
						"placeholder": logichop_text.variable,
						"map": 0
					},
					{
						"label": "Operator",
						"name": "operator",
						"type": "select",
						"options": {
							"==": logichop_text.is, 
							"!=": logichop_text.is_not
						}
					},
					{
						"label": "Value",
						"name": "value",
						"type": "text",
						"placeholder": logichop_text.value,
						"map": 1
					}
				],
				"info": logichop_text.query_se_info
			},
			"loggedin": {
				"label": logichop_text.user,
				"map": '{"#operator": [ {"var": "LoggedIn" }, true ] }',
				"lookup": "LoggedIn",
				"type": "default",
				"inputs": [
					{
						"label": "Operator",
						"name": "operator",
						"type": "select",
						"options": {
							"==": logichop_text.user_in, 
							"!=": logichop_text.user_out
						}
					}
				],
				"info": logichop_text.user_info
			},
			"date_weekday": {
				"label": logichop_text.weekday,
				"map": '{"#operator": [{"var": "Date.DayNumber" }, #day]}',
				"lookup": "Date.DayNumber",
				"type": "default",
				"inputs": [
					{
						"label": "Operator",
						"name": "operator",
						"type": "select",
						"options": self.operators
					},
					{
						"label": "Day",
						"name": "day",
						"type": "select",
						"options": {
							"1": logichop_text.weekday_1,
							"2": logichop_text.weekday_2, 
							"3": logichop_text.weekday_3, 
							"4": logichop_text.weekday_4, 
							"5": logichop_text.weekday_5, 
							"6": logichop_text.weekday_6, 
							"7": logichop_text.weekday_7 
						},
						"map": 1
					},
				
				],
				"info": logichop_text.weekday_info
			},
			"date_day": {
				"label": logichop_text.day,
				"map": '{"#operator": [{"var": "Date.Day" }, #day]}',
				"lookup": "Date.Day",
				"type": "default",
				"inputs": [
					{
						"label": "Operator",
						"name": "operator",
						"type": "select",
						"options": self.operators
					},
					{
						"label": "Day",
						"name": "day",
						"type": "select",
						"options": {
							"1": 1,
							"2": 2, 
							"3": 3, 
							"4": 4, 
							"5": 5, 
							"6": 6, 
							"7": 7,
							"8": 8, 
							"9": 9, 
							"10": 10, 
							"11": 11, 
							"12": 12, 
							"13": 13,
							"14": 14, 
							"15": 15, 
							"16": 16, 
							"17": 17, 
							"18": 18, 
							"19": 19, 
							"20": 20, 
							"21": 21, 
							"22": 22, 
							"23": 23,
							"24": 24, 
							"25": 25, 
							"26": 26, 
							"27": 27, 
							"28": 28, 
							"29": 29, 
							"30": 30, 
							"31": 31
						},
						"map": 1
					},
				
				],
				"info": logichop_text.day_info
			},
			"date_month": {
				"label": logichop_text.month,
				"map": '{"#operator": [{"var": "Date.Month" }, #month]}',
				"lookup": "Date.Month",
				"type": "default",
				"inputs": [
					{
						"label": "Operator",
						"name": "operator",
						"type": "select",
						"options": self.operators
					},
					{
						"label": "Month",
						"name": "month",
						"type": "select",
						"options": {
							"1": logichop_text.month_01,
							"2": logichop_text.month_02,
							"3": logichop_text.month_03, 
							"4": logichop_text.month_04,
							"5": logichop_text.month_05, 
							"6": logichop_text.month_06,
							"7": logichop_text.month_07, 
							"8": logichop_text.month_08,
							"9": logichop_text.month_09, 
							"10": logichop_text.month_10,
							"11": logichop_text.month_11,
							"12": logichop_text.month_12
						},
						"map": 1
					}
				],
				"info": logichop_text.month_info
			},
			"date_year": {
				"label": logichop_text.year,
				"map": '{"#operator": [{"var": "Date.Year" }, #year]}',
				"lookup": "Date.Year",
				"type": "default",
				"inputs": [
					{
						"label": "Operator",
						"name": "operator",
						"type": "select",
						"options": self.operators
					},
					{
						"label": "Year",
						"name": "year",
						"type": "select",
						"options": {
							"2016": "2016",
							"2017": "2017",
							"2018": "2018",
							"2019": "2019",
							"2020": "2020",
						},
						"map": 1
					}
				],
				"info": logichop_text.year_info
			},
			"date_hour": {
				"label": logichop_text.hour,
				"map": '{"#operator": [ {"var": "Date.Hour24" }, #hour ] }',
				"lookup": "Date.Hour24",
				"type": "default",
				"inputs": [
					{
						"label": "Operator",
						"name": "operator",
						"type": "select",
						"options": self.operators
					},
					{
						"label": "Hour",
						"name": "hour",
						"type": "select",
						"options": {
							"0": "12 am",
							"1": " 1 am",
							"2": " 2 am", 
							"3": " 3 am", 
							"4": " 4 am", 
							"5": " 5 am", 
							"6": " 6 am", 
							"7": " 7 am",
							"8": " 8 am", 
							"9": " 9 am", 
							"10": "10 am", 
							"11": "11 am", 
							"12": "12 pm", 
							"13": " 1 pm",
							"14": " 2 pm", 
							"15": " 3 pm", 
							"16": " 4 pm", 
							"17": " 5 pm", 
							"18": " 6 pm", 
							"19": " 7 pm", 
							"20": " 8 pm", 
							"21": " 9 pm", 
							"22": "10 pm", 
							"23": "11 pm"
						},
						"map": 1
					}
				],
				"info": logichop_text.hour_info
			},
			"date_minutes": {
				"label": logichop_text.minutes,
				"map": '{"#operator": [ {"var": "Date.Minutes" }, #minutes ] }',
				"lookup": "Date.Minutes",
				"type": "default",
				"inputs": [
					{
						"label": "Operator",
						"name": "operator",
						"type": "select",
						"options": self.operators
					},
					{
						"label": "Minutes",
						"name": "minutes",
						"type": "select",
						"options": {
							"0": ":00",
							"1": ":01",
							"2": ":02", 
							"3": ":03", 
							"4": ":04", 
							"5": ":05", 
							"6": ":06", 
							"7": ":07",
							"8": ":08", 
							"9": ":09", 
							"10": ":10", 
							"11": ":11", 
							"12": ":12", 
							"13": ":13",
							"14": ":14", 
							"15": ":15", 
							"16": ":16", 
							"17": ":17", 
							"18": ":18", 
							"19": ":19", 
							"20": ":20", 
							"21": ":21", 
							"22": ":22", 
							"23": ":23",
							"24": ":24", 
							"25": ":25", 
							"26": ":26", 
							"27": ":27", 
							"28": ":28", 
							"29": ":29", 
							"30": ":30", 
							"31": ":31", 
							"32": ":32", 
							"33": ":33", 
							"34": ":34", 
							"35": ":35",
							"36": ":36", 
							"37": ":37", 
							"38": ":38", 
							"39": ":39", 
							"40": ":40", 
							"41": ":41", 
							"42": ":42", 
							"43": ":43", 
							"44": ":44", 
							"45": ":45",
							"46": ":46", 
							"47": ":47", 
							"48": ":48", 
							"49": ":49", 
							"50": ":50", 
							"51": ":51", 
							"52": ":52", 
							"53": ":53",
							"54": ":54", 
							"55": ":55", 
							"56": ":56", 
							"57": ":57", 
							"58": ":58", 
							"59": ":59"
						},
						"map": 1
					}
				],
				"info": logichop_text.minutes_info
			},
			"date_ymd": {
				"label": logichop_text.date,
				"map": '{"#operator": [ {"var": "Date.Date" }, "#date"]}',
				"lookup": "Date.Date",
				"type": "default",
				"inputs": [
					{
						"label": "Operator",
						"name": "operator",
						"type": "select",
						"options": self.operators
					},
					{
						"label": "Date",
						"name": "date",
						"type": "date",
						"map": 1
					}
				],
				"info":  logichop_text.date_info
			},
			"path": {
				"label": logichop_text.path,
				"map": '{"#operator": [{"compare_array_slice": [[#pages], {"var": "Path"}]}, true]}',
				"lookup": "Path",
				"type": "default",
				"inputs": [
					{
						"label": "Operator",
						"name": "operator",
						"type": "select",
						"options": {
							"==": logichop_text.is, 
							"!=": logichop_text.is_not
						}
					},
					{
						"label": logichop_text.page_1,
						"name": "page-1",
						"type": "select",
						"options": logichop_pages,
						"map": 0
					},
					{
						"label": logichop_text.page_2,
						"name": "page-2",
						"type": "select",
						"options": logichop_pages,
						"map": 1
					},
					{
						"label": logichop_text.page_3,
						"name": "page-3",
						"type": "select",
						"options": logichop_pages,
						"map": 2
					},
					{
						"label": logichop_text.page_4,
						"name": "page-4",
						"type": "select",
						"options": logichop_pages,
						"map": 3
					},
					{
						"label": logichop_text.page_5,
						"name": "page-5",
						"type": "select",
						"options": logichop_pages,
						"map": 4
					}
				],
				"info": logichop_text.path_info
			}
		};
		
		self.conditions = jQuery.extend({}, self.conditions, logichop_integration_conditions); 
	};
	
	this.addCondition = function (name, value) {
		self.conditions[name] = value;
	}
	
	this.init = function (type) {
	
		self.condition_count++;
	
		if (self.condition_count == 1) {
			var logic = jQuery('<select class="' + self.condition_input_class + ' logic"><option value="if">' + logichop_text.if + '</option></select>');
		} else {
			var logic = jQuery('<select class="' + self.condition_input_class + ' logic"><option value="and">' + logichop_text.and + '</option><option value="or">' + logichop_text.or + '</option></select>');
		}
	
		var select = jQuery('<select class="' + self.condition_input_class + ' type"><option value="">' + logichop_text.select_type + '</option></select>');
		jQuery.each(self.conditions, function (key, value) {
			select.append( jQuery('<option></option>').attr('value', key).text(value.label));
		});
		
		var condition_set = '';
		if (type) {
			select.val(type);
			condition_set = 'loginchop-condition-set';
		}
		
		var buttons = '<a href="#" title="' + logichop_text.details + '" class="btn-info">' + logichop_text.details + '</a><div class="info"></div><a href="#" title="' + logichop_text.add_cond + '" class="btn-add button-secondary">+</a><a href="#" title="' + logichop_text.remove_cond + '" class="btn-remove"><small>' + logichop_text.remove + '</small></a></div>';
		var condition = jQuery('<div class="' + self.condition_class + ' logichop-condition ' + condition_set + '"><span class="params"></span>' + buttons);
		condition.prepend(select);
		condition.prepend(logic);
	
		jQuery('.logichop-conditions').append(condition);
		self.updateLogic(self.condition_logic);
		
		condition.children('.info').html(self.info_default);
		
		return condition;
	};
	
	this.refresh = function (type) {
		self.init(false);
	};
	
	this.display = function (element, condition, data) {
	
		var params = element.children('.params');
		var inputs = self.conditions[condition].inputs;
	
		params.html('');
	
		jQuery.each(inputs, function (key, input) {
			if (input.type == 'select') {
				var select = jQuery('<select class="' + self.condition_input_class + ' ' + input.name + '"></select>');
				if (input.name != 'operator') select.append( jQuery('<option value="">' + logichop_text.select + ' ' + input.label + '</option>'));
				jQuery.each(input.options, function (key, value) {
					select.append( jQuery('<option></option>').attr('value', key).text(value));
				});
				if (data) select.val(data[input.name]);
				params.append(select);
			} else if (input.type == 'datalist') {
				var data_input = jQuery('<input class="datalist ' + self.condition_input_class + ' ' + input.name + '" list="' + input.name + '">');
				var datalist = jQuery('<datalist id="' + input.name + '"></datalist>');
				if (input.name != 'operator') datalist.append( jQuery('<option value="">' + logichop_text.select + ' ' + input.label + '</option>'));
				jQuery.each(input.options, function (key, value) {
					datalist.append( jQuery('<option></option>').attr('value', value).attr('data-value', key));
				});
				if (data) {
					var data_value = datalist.find('[data-value="' + data[input.name] + '"]').attr('value');
					if (data_value) {
						data_input.val(data_value);
					} else {
						data_input.val(data[input.name]);
					}
				}
				params.append(data_input);
				params.append(datalist);
			} else {
				var value = '';
				var args = '';
				if (input.type == 'number') {
					value = 1;
					args = 'min="0" step="1"';
				}
				if (input.name == 'url') value = 'http://';
				if (data && data[input.name]) value = data[input.name];
				var placeholder = (input.placeholder) ? 'placeholder="' + input.placeholder + '"' : '';
				params.append( jQuery('<input class="' + self.condition_input_class + ' ' + input.name + '" type="' + input.type + '" value="' + value + '"' + args + ' ' + placeholder + '>') );
			}
		});
		
		if (self.conditions[condition].info) element.children('.info').html(self.conditions[condition].info);
	};

	this.formatJson = function () {
	
		var logic;
		var mappings = [];
		var condition;
	
		jQuery('.logichop-condition').each(function (index, element) {
			var type = jQuery(this).find('.type').val();
			logic = jQuery(this).find('.logic').val();
			var params = jQuery(this).find('.params');
		
			if (type) {
				var map = self.conditions[type].map;
		
				var pages = []; // ARRAY FOR PATH/HISTORY SUPPORT
		
				jQuery.each(self.conditions[type].inputs, function (key, input) {
					var value = params.find('.' + input.name).val();
					if (params.find('.' + input.name).hasClass('datalist')) {
						var option_data_value = jQuery('#' + input.name).find('option[value="' + value + '"]').attr('data-value');
						if (option_data_value) value = option_data_value;
					}
					if (input.name.search('page-') < 0) {
						var regex = new RegExp('#' + input.name, 'g');
						map = map.replace(regex, value);
					} else {
						if (value) pages.unshift(parseInt(value));
					}
				});
		
				if (pages) {
					var regex = new RegExp('#pages', 'g');
					map = map.replace(regex, pages);
				}
			
				mappings.push(map);
			}
		});
		
		if (logic == 'if') {
			condition = mappings.toString();
		} else {
			condition = '{"' +  logic + '": [' + mappings.toString() + ']}';
		}
		
		self.condition_json = condition;
		jQuery('.output').html(self.condition_json);
	};
	
	this.parseData = function (json) {
		var logic;
		var json_objects = [];
		var json_keys = Object.keys(json);
		
		if (json_keys[0] == 'and' || json_keys[0] == 'or') {
			json_objects = json[json_keys[0]];
			logic = json_keys[0];
		} else {
			json_objects.push(json);
		}
		
		jQuery.each(json_objects, function (index, data) {
			var data_keys = Object.keys(data);
			var data_values = [];
			var data_string = JSON.stringify(data);
	
			data_values.operator = data_keys[0];
			
			// LOOP THROUGH ALL CONDITIONS TO GET _slug_
			jQuery.each(self.conditions, function (key, value) {
				if (data_string.search(value.lookup) >= 0) {
					data_values.type = key;
					return false;
				}
			});
			
			// LOOP THROUGH ALL CONDITION INPUTS TO GET/SET VALUES
			jQuery.each(self.conditions[data_values.type].inputs, function (key, input) {
				if (input.name != 'operator') {
					if (data_values.type == 'path') {
						var path = data[data_values.operator][0].compare_array_slice[0].pop();
						data_values[input.name] = path;
					} else if (data_values.type == 'time_elapsed') {
						var value = data[data_values.operator][input.map];
						if (typeof(value) == 'object') {
							var time = value['-'][1].var;
							data_values[input.name] = time.substr(time.indexOf('.') + 1);
						} else {
							data_values[input.name] = value;
						}
					} else {
						var value = data[data_values.operator][input.map];
						if (typeof(value) == 'object') {
							var lookup_function = self.conditions[data_values.type].type;
							if (self[lookup_function] && typeof self[lookup_function] === 'function') {
								data_values[input.name] = self[lookup_function](value);
							}
						} else {
							data_values[input.name] = value;
						}
					}
				}
			});
	
			var element = self.init(data_values.type);
			self.display(element, data_values.type, data_values);
		});
	
		if (logic) self.updateLogic(logic);
		self.formatJson();
	};

	this.updateLogic = function (logic) {
		jQuery('.logic').each(function () {
			if (jQuery(this).val() != 'if') jQuery(this).val(logic);
		});
	};
	
	this.saveCondition = function () {
		jQuery('#excerpt').html(this.condition_json);
	};
	
	this.valueKeyExists = function (value) {
		try {
  			return value.key_exists[0];
		} catch (e) {
			return '';
		}
	}
	
	this.valueInArray = function (value) {
		try {
  			return value.in[0];
		} catch (e) {
			return '';
		}
	}
	
	this.valueSubStrIndex = function (value) {
		try {
  			return value.var.substr(value.var.indexOf('.') + 1);
		} catch (e) {
			return '';
		}
	}
	
	this.valueSubStrLastIndex = function (value) {
		try {
  			return value.var.substr(value.var.lastIndexOf('.') + 1);
		} catch (e) {
			return '';
		}
	}
}	

var logicHopCB = new logicHopConditionBuilder();
var logichop_integration_conditions = {};

jQuery(function($) {
	
	logicHopCB.setData();
	
	if (logichop_data) {
		logicHopCB.parseData(logichop_data);
	} else {
		logicHopCB.init(false);
	}
		
	$('.logichop-conditions').on('click', '.btn-add', function (e) { 
		logicHopCB.init(false);
		logicHopCB.saveCondition();
		e.preventDefault();
	});
	
	$('body').on('click', '.btn-save', function (e) {
		logicHopCB.saveCondition();
		e.preventDefault();
	});
	
	$('body').on('click', '.logichop-condition-logic', function (e) {
		if ($('.logichop-condition-excerpt').hasClass('logichop-condition-excerpt-hide')) {
			$(this).html(logichop_text.hide_logic);
			$('.logichop-condition-excerpt').removeClass('logichop-condition-excerpt-hide');
		} else {
			$(this).html(logichop_text.show_logic);
			$('.logichop-condition-excerpt').addClass('logichop-condition-excerpt-hide');
		}
		e.preventDefault();
	});
	
	$('body').on('click', '#logichop_css_condition', function (e) {
		if ($('.logichop-css').hasClass('logichop-condition-excerpt-hide')) {
			$('.logichop-css').removeClass('logichop-condition-excerpt-hide');
		} else {
			$('.logichop-css').addClass('logichop-condition-excerpt-hide');
		}
	});
	
	$('.logichop-conditions').on('change', '.type', function () {
		var type = $(this).val();
		if (type) {
			logicHopCB.display($(this).parent(), $(this).val(), false);
			$(this).parent().addClass('loginchop-condition-set');
		} else {
			$(this).parent().removeClass('loginchop-condition-set');
		}
	});
	
	$('.logichop-conditions').on('click', '.btn-remove', function (e) {
		$(this).parent().remove();
		logicHopCB.condition_count--;
		
		if ($('.logichop-condition').first().find('.logic').val() != 'if') {
			$('.logichop-condition').first().find('.logic').empty();
			$('.logichop-condition').first().find('.logic').append('<option value="if">' + logichop_text.if + '</option>');
		}
			
		if (logicHopCB.condition_count == 0) logicHopCB.init();
		logicHopCB.formatJson();
		logicHopCB.saveCondition(); 
		e.preventDefault();
	});
	
	$('.logichop-conditions').on('click', '.btn-info', function (e) {
		$(this).parent().find('.info').toggle();
		e.preventDefault();
	});
	
	$('.logichop-conditions').on('change', '.logic', function () {
		logicHopCB.condition_logic = $(this).val();
		logicHopCB.updateLogic(logicHopCB.condition_logic);
		logicHopCB.saveCondition();
	});
	
	$('.logichop-conditions').on('change keyup', 'input, select', function () {
		logicHopCB.formatJson();
		logicHopCB.saveCondition(); 
	});
});

