file = File.open('shortcodes.txt')

file.each_line do |line|
  shortcode = line.match(/(ms_[^\']*)/)
  puts "[shortcode_report prefix='#{shortcode}']"
end

file.close
